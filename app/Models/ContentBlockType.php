<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ContentBlockType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fields',
        'organisation_id',
        'is_default',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_default' => 'boolean',
    ];

    private static $coreFieldTypes = [
        'text', 'textarea', 'number', 'select', 'checkbox', 
        'radio', 'switch', 'date', 'time', 'file', 'email', 
        'password', 'url', 'tel', 'richtext'
    ];

    private static $validFieldTypes = [
        'text', 'textarea', 'number', 'select', 'checkbox', 
        'radio', 'switch', 'date', 'time', 'file', 'email', 
        'password', 'url', 'tel', 'content_block_array', 'richtext'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($contentBlockType) {
            if (!$contentBlockType->slug || $contentBlockType->isDirty('name')) {
                $contentBlockType->slug = Str::slug($contentBlockType->name);
            }

            $fields = $contentBlockType->fields;
            if (is_array($fields)) {
                foreach ($fields as &$field) {
                    if (!isset($field['label']) || !is_string($field['label'])) {
                        throw new \InvalidArgumentException(
                            "Each field must have a 'label' property of type string"
                        );
                    }

                    $field['slug'] = Str::slug($field['label']);

                    if (!isset($field['type'])) {
                        throw new \InvalidArgumentException(
                            "Each field must have a 'type' property"
                        );
                    }

                    if (!in_array($field['type'], self::$coreFieldTypes)) {
                        $referencedBlock = self::where('id', $field['type'])
                            ->where('organisation_id', $contentBlockType->organisation_id)
                            ->first();

                        if ($referencedBlock) {
                            $field['reference_block_type_id'] = $field['type'];
                            $field['type'] = 'content_block_array';
                        } else {
                            throw new \InvalidArgumentException(
                                "Invalid field type '{$field['type']}'. Must be one of: " . 
                                implode(', ', self::$coreFieldTypes) . 
                                " or a valid content block type ID from your organisation"
                            );
                        }
                    }
                }
                $contentBlockType->fields = $fields;
            }
        });
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(ContentBlock::class, 'type', 'slug');
    }

    public static function getDefaultTypes()
    {
        return static::where('is_default', true)->get();
    }

    public static function getOrganisationTypes(Organisation $organisation)
    {
        return static::where(function ($query) use ($organisation) {
            $query->where('organisation_id', $organisation->id)
                  ->orWhere('is_default', true);
        })->get();
    }

    public static function getArrayFieldOptions(Organisation $organisation)
    {
        return self::where('organisation_id', $organisation->id)
            ->get()
            ->map(fn (ContentBlockType $contentBlockType) => [
                'label' => 'Nested ' . $contentBlockType->name . ' blocks',
                'id' => $contentBlockType->id
            ])
            ->values()
            ->toArray();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
} 