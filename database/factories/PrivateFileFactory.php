<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\PrivateFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrivateFile>
 */
class PrivateFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contentType = $this->faker->randomElement(PrivateFile::CONTENT_TYPES);
        
        $fileExtensions = [
            'ebook' => ['pdf', 'epub', 'mobi'],
            'audiobook' => ['mp3', 'm4b', 'mp4'],
            'video' => ['mp4', 'avi', 'mov', 'mkv'],
            'document' => ['pdf', 'doc', 'docx', 'txt'],
            'other' => ['zip', 'rar', '7z'],
        ];

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'epub' => 'application/epub+zip',
            'mobi' => 'application/x-mobipocket-ebook',
            'mp3' => 'audio/mpeg',
            'm4b' => 'audio/mp4',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
        ];

        $extension = $this->faker->randomElement($fileExtensions[$contentType]);
        $fileName = $this->faker->slug() . '.' . $extension;
        
        return [
            'organisation_id' => Organisation::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'content_type' => $contentType,
            'file_path' => 'private-files/' . $this->faker->year() . '/' . $this->faker->month() . '/' . $fileName,
            'file_name' => $fileName,
            'mime_type' => $mimeTypes[$extension] ?? 'application/octet-stream',
            'file_size' => $this->faker->numberBetween(1024 * 100, 1024 * 1024 * 500), // 100KB to 500MB
            'metadata' => $this->generateMetadata($contentType),
            'active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Generate metadata based on content type
     *
     * @param string $contentType
     * @return array
     */
    private function generateMetadata(string $contentType): array
    {
        $metadata = [
            'uploaded_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
        ];

        switch ($contentType) {
            case 'ebook':
                $metadata['pages'] = $this->faker->numberBetween(50, 800);
                $metadata['isbn'] = $this->faker->isbn13();
                $metadata['author'] = $this->faker->name();
                $metadata['publisher'] = $this->faker->company();
                break;
                
            case 'audiobook':
                $metadata['duration_seconds'] = $this->faker->numberBetween(3600, 36000); // 1 hour to 10 hours
                $metadata['narrator'] = $this->faker->name();
                $metadata['bitrate'] = $this->faker->randomElement(['128', '192', '256', '320']);
                $metadata['chapters'] = $this->faker->numberBetween(5, 50);
                break;
                
            case 'video':
                $metadata['duration_seconds'] = $this->faker->numberBetween(300, 7200); // 5 minutes to 2 hours
                $metadata['resolution'] = $this->faker->randomElement(['720p', '1080p', '4K']);
                $metadata['fps'] = $this->faker->randomElement([24, 25, 30, 60]);
                break;
                
            case 'document':
                $metadata['pages'] = $this->faker->numberBetween(1, 200);
                $metadata['language'] = $this->faker->randomElement(['en', 'es', 'fr', 'de']);
                break;
        }

        return $metadata;
    }

    /**
     * Indicate that the file is an ebook.
     */
    public function ebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'ebook',
        ]);
    }

    /**
     * Indicate that the file is an audiobook.
     */
    public function audiobook(): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'audiobook',
        ]);
    }

    /**
     * Indicate that the file is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
