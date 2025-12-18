/* eslint-disable @typescript-eslint/no-explicit-any */
import { Button } from '@/components/ui/button';
import { ContentBlockType } from '@/types/models';
import { Plus } from 'lucide-react';

export function findFilePreview(blockField: any, content_with_urls: any) {
    if (blockField.type === 'file') {
        return content_with_urls?.[blockField.slug + '_url'] ?? null;
    }
    return null;
}

export function InputRenderer({
    blockField,
    field,
    filePreview,
    form,
    contentBlockTypes = [],
    i,
    j,
    currentName,
}: {
    blockField: any;
    field: any;
    filePreview?: string | null;
    form?: any;
    contentBlockTypes?: ContentBlockType[];
    i?: number;
    j?: number;
    currentName?: string;
}) {
    switch (blockField.type) {
        case 'text':
            return <field.FormInput name={field.name} label={blockField.label} />;
        case 'textarea':
            return <field.FormTextarea name={field.name} label={blockField.label} />;
        case 'select':
            return <field.FormSelect name={field.name} label={blockField.label} options={blockField.options || []} />;
        case 'file':
            return <field.FormFile name={field.name} label={blockField.label} filePreview={filePreview} />;
        case 'checkbox':
            return <field.FormCheckbox name={field.name} label={blockField.label} />;
        case 'radio':
            return <field.FormRadioGroup name={field.name} label={blockField.label} options={blockField.options || []} />;
        case 'switch':
            return <field.FormSwitch name={field.name} label={blockField.label} />;
        case 'email':
            return <field.FormEmail name={field.name} label={blockField.label} />;
        case 'password':
            return <field.FormPassword name={field.name} label={blockField.label} />;
        case 'url':
            return <field.FormUrl name={field.name} label={blockField.label} />;
        case 'phone':
            return <field.FormPhone name={field.name} label={blockField.label} />;
        case 'date':
            return <field.FormDate name={field.name} label={blockField.label} />;
        case 'time':
            return <field.FormTime name={field.name} label={blockField.label} />;
        case 'richtext':
            return <field.FormRichText name={field.name} label={blockField.label} />;
        case 'content_block_array': {
            const referencedBlockType = contentBlockTypes.find((c) => `${c.id}` === `${blockField.reference_block_type_id}`);

            const referencedBlockTypeFields = referencedBlockType?.fields ?? [];
            console.log('referencedBlockTypeFields', referencedBlockTypeFields);
            console.log('field', field);
            return (
                <div className="space-y-4">
                    <h5 className="text-md font-medium">{referencedBlockType?.name}</h5>
                    <form.Field
                        name={field.name}
                        mode="array"
                        //defaultValue={[]}
                        children={(nestedFields: any) => (
                            <div className="space-y-4">
                                {console.log('nestedFields', nestedFields?.state?.value)}
                                {(Array.isArray(nestedFields?.state?.value) ? nestedFields.state.value : []).map((_: any, k: any) => (
                                    <div key={k} className="space-y-4 rounded-md border p-3">
                                        {referencedBlockTypeFields.map((nestedField) => (
                                            <form.AppField
                                                key={`${i}_${nestedField.slug}_${k}`}
                                                name={`${currentName}[${k}].${nestedField.slug}`}
                                                children={(field: any) => {
                                                    return (
                                                        <InputRenderer
                                                            blockField={nestedField}
                                                            field={field}
                                                            currentName={`${currentName}[${k}].${nestedField.slug}`}
                                                            filePreview={filePreview}
                                                            form={form}
                                                            contentBlockTypes={contentBlockTypes}
                                                            i={j}
                                                            j={k}
                                                        />
                                                    );
                                                }}
                                            />
                                        ))}
                                        <Button variant="destructive" size="sm" type="button" onClick={() => nestedFields.removeValue(k)}>
                                            Remove
                                        </Button>
                                    </div>
                                ))}
                                <Button
                                    variant="outline"
                                    size="sm"
                                    type="button"
                                    onClick={() => {
                                        const initialContent: Record<string, string> = {};
                                        referencedBlockType?.fields.forEach((field) => {
                                            initialContent[field.slug] = '';
                                        });
                                        nestedFields.pushValue(initialContent);
                                    }}
                                >
                                    Add {referencedBlockType?.name} <Plus className="h-4 w-4" />
                                </Button>
                            </div>
                        )}
                    />
                </div>
            );
        }
        default:
            console.warn(`Unhandled field type: ${blockField.type}`);
            return <div>Unhandled field type: {blockField.type}</div>;
    }
}
