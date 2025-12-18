import { ReusableForm, useAppForm } from '@/components/forms/form-context';
import { findFilePreview, InputRenderer } from '@/components/forms/input-renderer';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import type { ContentBlock, ContentBlockType } from '@/types/models';
import { router } from '@inertiajs/react';
import { type } from 'arktype';

const ContentBlockSchema = type({
    content_block_type_id: 'string',
    content: 'object',
    description: 'string?',
});

type ContentBlockSchema = typeof ContentBlockSchema.infer;

interface Props extends ReusableForm<ContentBlockSchema> {
    contentBlockTypes: ContentBlockType[];
    contentBlock?: ContentBlock;
}

export default function ContentBlockForm({ contentBlockTypes, contentBlock, mode }: Props) {
    const form = useAppForm({
        defaultValues: {
            content_block_type_id: contentBlock?.content_block_type_id?.toString() || '',
            content: contentBlock?.content_with_urls || {},
            description: contentBlock?.description || '',
        },
        onSubmit: async (data) => {
            await new Promise((resolve) => setTimeout(resolve, 1000));
            if (mode === 'create') {
                router.post(route('content-blocks.store'), data.value);
            } else {
                router.put(route('content-blocks.update', contentBlock?.id), data.value);
            }
        },
        validators: {
            onChange: ContentBlockSchema,
        },
    });

    return (
        <div className="space-y-6">
            <Card>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        form.handleSubmit();
                    }}
                    className="space-y-6"
                >
                    <CardHeader>
                        <CardTitle>Content Block Details</CardTitle>
                        <CardDescription>{contentBlock ? 'Edit Content Block' : 'Create Content Block'}</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <form.AppField
                            name="content_block_type_id"
                            children={(field) => {
                                return (
                                    <field.FormSelect
                                        name={field.name}
                                        label="Content Block Type"
                                        options={contentBlockTypes.map((type) => ({
                                            value: type.id.toString(),
                                            label: type.name,
                                        }))}
                                    />
                                );
                            }}
                        />
                        <form.AppField
                            name="description"
                            children={(field) => {
                                return <field.FormTextarea name={field.name} label="Description" />;
                            }}
                        />
                        <form.Subscribe
                            selector={(state) => state.values.content_block_type_id}
                            children={(content_block_type_id) => {
                                const selectedBlock = contentBlockTypes.find((type) => `${type.id}` === content_block_type_id);
                                const blockFields = selectedBlock?.fields;
                                if (!blockFields) {
                                    return null;
                                }
                                return (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>{selectedBlock?.name}</CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            {blockFields?.map((blockField, i) => (
                                                <form.AppField
                                                    key={`field_${selectedBlock?.id}_${i}`}
                                                    name={`content.${blockField.slug}`}
                                                    children={(field) => {
                                                        return (
                                                            <InputRenderer
                                                                blockField={blockField}
                                                                field={field}
                                                                form={form}
                                                                filePreview={findFilePreview(blockField, contentBlock?.content_with_urls)}
                                                                currentName={`content.${blockField.slug}`}
                                                                contentBlockTypes={contentBlockTypes}
                                                                i={i}
                                                                j={i + 1}
                                                            />
                                                        );
                                                    }}
                                                />
                                            ))}
                                        </CardContent>
                                    </Card>
                                );
                            }}
                        />
                    </CardContent>
                    <CardFooter>
                        <form.AppForm>
                            <form.SubscribeButton label={contentBlock ? 'Update Content Block' : 'Create Content Block'} />
                        </form.AppForm>
                    </CardFooter>
                </form>
            </Card>
        </div>
    );
}
