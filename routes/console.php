<?php

use App\Models\Equipment;
use App\Models\EquipmentDocument;
use App\Models\EquipmentDocumentType;
use App\Models\EquipmentImage;
use App\Models\EquipmentRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('equipment:migrate-media-paths {--dry-run : Only show what would be moved}', function () {
    $disk = Storage::disk('public');
    $dryRun = (bool) $this->option('dry-run');

    $folderSlug = static function (Equipment $equipment): string {
        $base = trim(((string) $equipment->number).' '.((string) $equipment->name));
        $base = preg_replace('/[^\p{L}\p{N}\s_-]+/u', '', $base) ?? '';
        $base = preg_replace('/\s+/u', '_', trim($base)) ?? '';
        $base = trim($base, '_- ');
        if ($base === '') {
            $base = 'оборудование_'.$equipment->id;
        }

        return Str::limit($base, 80, '');
    };

    $docDir = static function (string $typeCode): string {
        return match ($typeCode) {
            'instruction' => 'инструкции',
            'registration_certificate', 'ru_scan' => 'регистрационные_удостоверения',
            'commissioning_act' => 'акты_ввода_в_эксплуатацию',
            'utilization_act' => 'акты_утилизации',
            'signed_report_act' => 'прочие_документы',
            default => 'прочие_документы',
        };
    };

    $equipmentMap = Equipment::withTrashed()
        ->get(['id', 'number', 'name'])
        ->keyBy('id');

    $moveToUniqueTarget = function (string $sourcePath, string $targetDir) use ($disk): string {
        $targetPath = rtrim($targetDir, '/').'/'.basename($sourcePath);
        if (! $disk->exists($targetPath)) {
            return $targetPath;
        }
        $ext = pathinfo($targetPath, PATHINFO_EXTENSION);
        $nameOnly = pathinfo($targetPath, PATHINFO_FILENAME);
        $suffix = now()->format('Ymd_His').'_'.Str::random(5);

        return rtrim($targetDir, '/').'/'.$nameOnly.'_'.$suffix.($ext !== '' ? '.'.$ext : '');
    };

    $moved = 0;
    $missing = 0;
    $skipped = 0;

    $this->info(($dryRun ? '[DRY-RUN] ' : '').'Миграция файлов в структуру "оборудование/<номер_название>/..."');

    EquipmentImage::query()->orderBy('id')->chunkById(200, function ($rows) use (
        &$moved, &$missing, &$skipped, $dryRun, $disk, $equipmentMap, $folderSlug, $moveToUniqueTarget
    ): void {
        foreach ($rows as $row) {
            $old = trim((string) $row->image);
            if ($old === '') {
                $skipped++;

                continue;
            }
            if (str_starts_with($old, 'оборудование/')) {
                $skipped++;

                continue;
            }
            $equipment = $equipmentMap->get((int) $row->equipment_id);
            if (! $equipment instanceof Equipment) {
                $skipped++;

                continue;
            }
            $targetDir = 'оборудование/'.$folderSlug($equipment).'/фото';
            $target = $moveToUniqueTarget($old, $targetDir);

            if (! $disk->exists($old)) {
                $missing++;

                continue;
            }

            if (! $dryRun) {
                $disk->move($old, $target);
                $row->image = $target;
                $row->save();
            }
            $moved++;
        }
    });

    EquipmentDocument::query()
        ->with('documentType')
        ->orderBy('id')
        ->chunkById(200, function ($rows) use (
            &$moved, &$missing, &$skipped, $dryRun, $disk, $equipmentMap, $folderSlug, $docDir, $moveToUniqueTarget
        ): void {
            foreach ($rows as $row) {
                $old = trim((string) $row->document);
                if ($old === '') {
                    $skipped++;

                    continue;
                }
                $needsSignedDirRename = str_contains($old, '/подписанные_акты_списания_перемещения/');
                if (str_starts_with($old, 'оборудование/') && ! $needsSignedDirRename) {
                    $skipped++;

                    continue;
                }
                $equipment = $row->equipment()->first();
                if (! $equipment instanceof Equipment) {
                    $skipped++;

                    continue;
                }
                $typeCode = (string) ($row->documentType?->code ?? '');
                $targetDir = 'оборудование/'.$folderSlug($equipment).'/'.$docDir($typeCode);
                $target = $moveToUniqueTarget($old, $targetDir);

                if (! $disk->exists($old)) {
                    $missing++;

                    continue;
                }

                if (! $dryRun) {
                    $disk->move($old, $target);
                    $row->document = $target;
                    $row->save();
                }
                $moved++;
            }
        });

    EquipmentRequest::query()->orderBy('id')->chunkById(200, function ($rows) use (
        &$moved, &$missing, &$skipped, $dryRun, $disk, $equipmentMap, $folderSlug, $moveToUniqueTarget
    ): void {
        foreach ($rows as $row) {
            $old = trim((string) $row->photo);
            if ($old === '') {
                $skipped++;

                continue;
            }
            if (str_starts_with($old, 'оборудование/')) {
                $skipped++;

                continue;
            }
            $equipment = $equipmentMap->get((int) $row->equipment_id);
            if (! $equipment instanceof Equipment) {
                $skipped++;

                continue;
            }
            $targetDir = 'оборудование/'.$folderSlug($equipment).'/фото_заявок_на_списание';
            $target = $moveToUniqueTarget($old, $targetDir);

            if (! $disk->exists($old)) {
                $missing++;

                continue;
            }

            if (! $dryRun) {
                $disk->move($old, $target);
                $row->photo = $target;
                $row->save();
            }
            $moved++;
        }
    });

    $this->newLine();
    $this->info('Готово.');
    $this->line('Перемещено: '.$moved);
    $this->line('Пропущено: '.$skipped);
    $this->line('Не найдено файлов на диске: '.$missing);
    if ($dryRun) {
        $this->warn('Это был DRY-RUN. Для реального переноса выполните без --dry-run.');
    }
})->purpose('Move legacy equipment media into per-equipment folders');

/**
 * После migrate:fresh / очистки БД файлы в public/storage/оборудование/... остаются без записей в equipment_images / equipment_documents.
 * Команда находит файлы в ожидаемых подпапках и создаёт строки в БД (пути как при загрузке через форму).
 */
Artisan::command('equipment:import-orphan-files {--dry-run : Показать без записи в БД}', function () {
    $disk = Storage::disk('public');
    $dryRun = (bool) $this->option('dry-run');

    $folderSlug = static function (Equipment $equipment): string {
        $base = trim(((string) $equipment->number).' '.((string) $equipment->name));
        $base = preg_replace('/[^\p{L}\p{N}\s_-]+/u', '', $base) ?? '';
        $base = preg_replace('/\s+/u', '_', trim($base)) ?? '';
        $base = trim($base, '_- ');
        if ($base === '') {
            $base = 'оборудование_'.$equipment->id;
        }

        return Str::limit($base, 80, '');
    };

    $docFolderToType = [
        'инструкции' => 'instruction',
        'регистрационные_удостоверения' => 'registration_certificate',
        'акты_ввода_в_эксплуатацию' => 'commissioning_act',
        'акты_утилизации' => 'utilization_act',
        'прочие_документы' => 'signed_report_act',
    ];

    $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $docExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    $importedImg = 0;
    $importedDoc = 0;
    $skipped = 0;

    Equipment::query()->orderBy('id')->chunkById(50, function ($equipments) use (
        $disk,
        $dryRun,
        $folderSlug,
        $docFolderToType,
        $imageExt,
        $docExt,
        &$importedImg,
        &$importedDoc,
        &$skipped
    ): void {
        foreach ($equipments as $equipment) {
            $root = 'оборудование/'.$folderSlug($equipment);
            if (! $disk->exists($root)) {
                continue;
            }

            $photoDir = $root.'/фото';
            if ($disk->exists($photoDir)) {
                foreach ($disk->files($photoDir) as $relPath) {
                    $relPath = str_replace('\\', '/', $relPath);
                    $base = basename($relPath);
                    if ($base === '' || str_starts_with($base, '.')) {
                        continue;
                    }
                    $ext = strtolower((string) pathinfo($relPath, PATHINFO_EXTENSION));
                    if (! in_array($ext, $imageExt, true)) {
                        continue;
                    }
                    if (EquipmentImage::query()->where('equipment_id', $equipment->id)->where('image', $relPath)->exists()) {
                        $skipped++;

                        continue;
                    }
                    if ($dryRun) {
                        $this->line("[dry-run] фото: {$equipment->id} {$relPath}");
                        $importedImg++;

                        continue;
                    }
                    EquipmentImage::query()->create([
                        'equipment_id' => $equipment->id,
                        'image' => $relPath,
                    ]);
                    $importedImg++;
                }
            }

            foreach ($docFolderToType as $sub => $typeCode) {
                $typeId = EquipmentDocumentType::idForCode($typeCode);
                if ($typeId === null) {
                    continue;
                }
                $dir = $root.'/'.$sub;
                if (! $disk->exists($dir)) {
                    continue;
                }
                foreach ($disk->files($dir) as $relPath) {
                    $relPath = str_replace('\\', '/', $relPath);
                    $base = basename($relPath);
                    if ($base === '' || str_starts_with($base, '.')) {
                        continue;
                    }
                    $ext = strtolower((string) pathinfo($relPath, PATHINFO_EXTENSION));
                    if (! in_array($ext, $docExt, true)) {
                        continue;
                    }
                    if (EquipmentDocument::query()
                        ->where('document', $relPath)
                        ->whereHas('equipment', fn ($q) => $q->whereKey($equipment->id))
                        ->exists()) {
                        $skipped++;

                        continue;
                    }
                    $abs = $disk->path($relPath);
                    $mtime = is_file($abs) ? \Carbon\Carbon::createFromTimestamp((int) filemtime($abs)) : now();
                    if ($dryRun) {
                        $this->line("[dry-run] документ: {$equipment->id} {$relPath} ({$typeCode})");
                        $importedDoc++;

                        continue;
                    }
                    $document = EquipmentDocument::query()->create([
                        'document' => $relPath,
                        'name' => $base,
                        'document_type_id' => $typeId,
                        'uploaded_at' => $mtime,
                    ]);
                    $equipment->documents()->syncWithoutDetaching([$document->id]);
                    $importedDoc++;
                }
            }
        }
    });

    $this->newLine();
    $this->info(($dryRun ? '[DRY-RUN] ' : '').'Импорт «сиротских» файлов завершён.');
    $this->line('Добавлено фото: '.$importedImg);
    $this->line('Добавлено документов: '.$importedDoc);
    $this->line('Пропущено (уже в БД или не подошло): '.$skipped);
    if ($dryRun) {
        $this->warn('Запустите без --dry-run для записи в базу.');
    }
})->purpose('Создать записи БД для файлов в storage/оборудование после очистки базы');

/**
 * Одноразовая раскладка уже сохранённых файлов по новой структуре каталога:
 * 1) регистрационное удостоверение
 * 2) инструкция на русском
 * 3) акт ввода в эксплуатацию
 * 4) инвентарные номера/<номер>/фото|прочие_документы
 */
Artisan::command('equipment:rebuild-catalog-by-inventory {--dry-run : Только показать изменения без переноса}', function () {
    $disk = Storage::disk('public');
    $dryRun = (bool) $this->option('dry-run');

    $catalogRoot = 'каталог_оборудования';
    $typeToDir = [
        'registration_certificate' => $catalogRoot.'/1_регистрационное_удостоверение',
        'ru_scan' => $catalogRoot.'/1_регистрационное_удостоверение',
        'instruction' => $catalogRoot.'/2_инструкция_на_русском_языке',
        'commissioning_act' => $catalogRoot.'/3_акт_ввода_в_эксплуатацию',
    ];

    $normalizeInventory = static function (Equipment $equipment): string {
        $inventory = trim((string) $equipment->inventory_number);
        if ($inventory === '') {
            $inventory = 'без_инвентарного_номера_'.$equipment->id;
        }
        $inventory = preg_replace('/[^\p{L}\p{N}\s._\-№]/u', '_', $inventory) ?? '';
        $inventory = trim(preg_replace('/_+/u', '_', $inventory) ?? '', '_ ');

        return $inventory !== '' ? $inventory : ('без_инвентарного_номера_'.$equipment->id);
    };

    $moveToDir = static function (string $sourcePath, string $targetDir, ?string $prefix = null) use ($disk): string {
        $sourcePath = trim(str_replace('\\', '/', $sourcePath), '/');
        $targetDir = trim(str_replace('\\', '/', $targetDir), '/');
        $basename = basename($sourcePath);
        $safePrefix = trim((string) $prefix);
        if ($safePrefix !== '' && ! str_starts_with($basename, $safePrefix.'_')) {
            $basename = $safePrefix.'_'.$basename;
        }
        $target = $targetDir.'/'.$basename;
        if ($sourcePath === $target) {
            return $sourcePath;
        }
        if ($disk->exists($target)) {
            $target = $targetDir.'/'.now()->format('Ymd_His').'_'.Str::random(5).'_'.$basename;
        }

        return $target;
    };

    $moved = 0;
    $missing = 0;
    $skipped = 0;
    $sharedSkipped = 0;

    Equipment::query()->orderBy('id')->chunkById(100, function ($equipments) use (
        $disk, $dryRun, $catalogRoot, $typeToDir, $normalizeInventory, $moveToDir, &$moved, &$missing, &$skipped, &$sharedSkipped
    ): void {
        foreach ($equipments as $equipment) {
            $inventoryPrefix = $normalizeInventory($equipment);
            $inventoryFolder = $catalogRoot.'/4_инвентарные_номера/'.$inventoryPrefix;

            foreach ($equipment->images()->get() as $image) {
                $old = trim((string) $image->image);
                if ($old === '') {
                    $skipped++;
                    continue;
                }
                if (! $disk->exists($old)) {
                    $missing++;
                    continue;
                }
                $target = $moveToDir($old, $inventoryFolder.'/фото', $inventoryPrefix);
                if ($old === $target) {
                    $skipped++;
                    continue;
                }
                if (! $dryRun) {
                    $disk->makeDirectory($inventoryFolder.'/фото');
                    $disk->move($old, $target);
                    $image->image = $target;
                    $image->save();
                }
                $moved++;
            }

            foreach ($equipment->documents()->with('documentType')->get() as $doc) {
                $old = trim((string) $doc->document);
                if ($old === '') {
                    $skipped++;
                    continue;
                }
                if (! $disk->exists($old)) {
                    $missing++;
                    continue;
                }

                $typeCode = (string) ($doc->documentType?->code ?? $doc->type ?? '');
                $targetDir = $typeToDir[$typeCode] ?? ($inventoryFolder.'/прочие_документы');

                // Общий документ, привязанный к нескольким оборудованиям, нельзя однозначно положить в
                // папку одного инвентарного номера: оставляем на месте и пропускаем.
                if (! isset($typeToDir[$typeCode]) && $doc->equipment()->count() > 1) {
                    $sharedSkipped++;
                    continue;
                }

                $target = $moveToDir($old, $targetDir, isset($typeToDir[$typeCode]) ? null : $inventoryPrefix);
                if ($old === $target) {
                    $skipped++;
                    continue;
                }

                if (! $dryRun) {
                    $disk->makeDirectory($targetDir);
                    $disk->move($old, $target);
                    $doc->document = $target;
                    $doc->save();
                }
                $moved++;
            }

            foreach ($equipment->requests()->whereNotNull('photo')->get() as $requestRow) {
                $old = trim((string) $requestRow->photo);
                if ($old === '') {
                    $skipped++;
                    continue;
                }
                if (! $disk->exists($old)) {
                    $missing++;
                    continue;
                }
                $targetDir = $inventoryFolder.'/прочие_документы';
                $target = $moveToDir($old, $targetDir, $inventoryPrefix);
                if ($old === $target) {
                    $skipped++;
                    continue;
                }
                if (! $dryRun) {
                    $disk->makeDirectory($targetDir);
                    $disk->move($old, $target);
                    $requestRow->photo = $target;
                    $requestRow->save();
                }
                $moved++;
            }
        }
    });

    $this->newLine();
    $this->info(($dryRun ? '[DRY-RUN] ' : '').'Перераскладка каталога завершена.');
    $this->line('Перемещено: '.$moved);
    $this->line('Пропущено (уже на месте/пустые пути): '.$skipped);
    $this->line('Пропущено общих документов (связано с несколькими оборудованиями): '.$sharedSkipped);
    $this->line('Не найдено на диске: '.$missing);
    if ($dryRun) {
        $this->warn('Это проверочный запуск. Для реального переноса выполните команду без --dry-run.');
    }
})->purpose('Разложить существующие файлы по новой структуре каталогов с инвентарными номерами');
