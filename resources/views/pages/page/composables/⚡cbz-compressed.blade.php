<?php

use Livewire\Component;

use Livewire\WithFileUploads;
use Spatie\Image\Image;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

new class extends Component
{
use WithFileUploads;

    public $cbzFile;

    public function processCbz()
    {
        $this->validate([
            'cbzFile' => 'required|file|max:102400', // Máximo 100MB
        ]);

        // 1. Crear rutas temporales
        $originalName = $this->cbzFile->getClientOriginalName();
        $tempPath = $this->cbzFile->getRealPath();
        $extractPath = storage_path('app/temp/' . uniqid());
        $outputPath = storage_path('app/public/compressed_' . $originalName);

        // $outputDirectory = storage_path('app/public/compressed_files');
        // if (!file_exists($outputDirectory)) {
        //     mkdir($outputDirectory, 0755, true);
        // }
        // $outputPath = $outputDirectory . '/' . $originalName;

        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $zip = new ZipArchive;
        
        if ($zip->open($tempPath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();

            // 2. Procesar y comprimir imágenes
            $files = scandir($extractPath);
            foreach ($files as $file) {
                $filePath = $extractPath . '/' . $file;
                
// Validar que sea una imagen
    if (is_file($filePath) && @is_array(getimagesize($filePath))) {
        Image::load($filePath)
            ->quality(50) // Bajamos a 50 para que el cambio sea evidente en la prueba
            ->optimize()   // Intenta optimizar sin pérdida visual extrema
            ->save($filePath); // Sobrescribimos el archivo extraído
    }
            }

            // 3. Empaquetar de nuevo en un CBZ
            // $newZip = new ZipArchive;
            // if ($newZip->open($outputPath, ZipArchive::CREATE) === TRUE) {
            //     foreach (scandir($extractPath) as $file) {
            //         if ($file !== '.' && $file !== '..') {
            //             $newZip->addFile($extractPath . '/' . $file, $file);
            //         }
            //     }
            //     $newZip->close();
            // }
// ... (después de comprimir las imágenes)

$newZip = new \ZipArchive;
// Usamos un nombre temporal simple para evitar caracteres especiales en el path
$tempZipName = 'temp_comp_' . time() . '.cbz';
$outputPath = storage_path('app/public/' . $tempZipName);

if ($newZip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($extractPath),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            // El segundo parámetro es el nombre dentro del ZIP
            $relativePath = substr($filePath, strlen($extractPath) + 1);
            $newZip->addFile($filePath, $relativePath);
        }
    }
    
    $newZip->close(); // Aquí se guarda físicamente
}

// Verificación manual antes de descargar
if (!file_exists($outputPath)) {
    session()->flash('error', 'El archivo no se pudo generar en: ' . $outputPath);
    return;
}

// Limpiar temporales (la carpeta de extracción)
$this->cleanup($extractPath);

// Descarga con el nombre original que quería el usuario
return response()->download($outputPath, $originalName)->deleteFileAfterSend(true);
                
        }

        session()->flash('error', 'No se pudo procesar el archivo.');
    }

    private function cleanup($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object))
                        $this->cleanup($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }
};
?>

<div>
    <div>
        <form wire:submit.prevent="processCbz">
            <input type="file" wire:model="cbzFile" accept=".cbz">
            
            <button type="submit" wire:loading.attr="disabled">
                Comprimir y Descargar
            </button>

            <div wire:loading wire:target="cbzFile">Subiendo...</div>
            <div wire:loading wire:target="processCbz">Procesando imágenes, por favor espera...</div>
        </form>
    </div>
</div>