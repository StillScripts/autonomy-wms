import { useAppForm, type ReusableForm } from '@/components/forms/form-context';
import { InputRenderer } from '@/components/forms/input-renderer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { ContentBlockType, Page as PageModel, Website } from '@/types/models';
import { Transition } from '@headlessui/react';
import { router } from '@inertiajs/react';
import { type } from 'arktype';
import { ChevronDown, ChevronUp, Plus, Trash } from 'lucide-react';

const Page = type({
    title: 'string',
    description: 'string?',
});

type PageSchema = typeof Page.infer;

interface PageFormProps extends ReusableForm<PageSchema> {
    website: Website;
    contentBlockTypes: ContentBlockType[];
    page?: PageModel;
    defaultValues?: {
        title: string;
        description: string;
        contentBlocks: {
            content_block_type_id: string;
            content: {
                [key: string]: string;
            };
        }[];
    };
}

export function PageForm({
    mode,
    website,
    contentBlockTypes,
    defaultValues = {
        title: '',
        description: '',
        contentBlocks: [] as { content_block_type_id: string; content: { [key: string]: string } }[],
    },
    page,
}: PageFormProps) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            if (mode === 'create') {
                router.post(route('websites.pages.store', website.id), data.value);
            } else {
                router.post(route('websites.pages.update', { website: website.id, page: page?.id }), {
                    ...data.value,
                    _method: 'PUT',
                });
            }
        },
        validators: {
            onChange: Page,
        },
    });

    const sortedContentBlockTypes = contentBlockTypes.sort((a, b) => a.name.localeCompare(b.name));

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-xl">{mode === 'create' ? 'New Page' : 'Edit Page'}</CardTitle>
            </CardHeader>
            <CardContent>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        form.handleSubmit();
                    }}
                    className="space-y-6"
                >
                    <form.AppField
                        name="title"
                        children={(field) => {
                            return <field.FormInput name={field.name} label="Page title" required placeholder="My New Page" />;
                        }}
                    />
                    <form.AppField
                        name="description"
                        children={(field) => {
                            return <field.FormTextarea name={field.name} label="Description" placeholder="A description of the page (optional)" />;
                        }}
                    />
                    <Separator />
                    <form.Field
                        name="contentBlocks"
                        mode="array"
                        children={(fields) => (
                            <div className="space-y-4">
                                <h4 className="text-lg font-medium">Page Content</h4>
                                <div className="space-y-4">
                                    {!fields.state.value.length ? (
                                        <span className="text-muted-foreground text-sm">No page content found.</span>
                                    ) : (
                                        fields.state.value.map((_, i) => {
                                            const blockType = contentBlockTypes.find((c) => c.id.toString() == _.content_block_type_id);
                                            const blockFields = blockType?.fields;
                                            const currentField = fields.state.value[i];
                                            let filePreview = null;
                                            if (currentField) {
                                                const urlKey = Object.keys(currentField?.content ?? {}).filter((key) => key.endsWith('_url'));
                                                if (urlKey) {
                                                    // @ts-expect-error we'll look deeper into validating the file input later
                                                    filePreview = currentField['content'][urlKey];
                                                }
                                            }
                                            return (
                                                <Card key={i}>
                                                    <CardHeader>
                                                        <CardTitle>{blockType?.name}</CardTitle>
                                                    </CardHeader>
                                                    <CardContent className="space-y-4">
                                                        {blockFields?.map((blockField, j) => (
                                                            <form.AppField
                                                                key={`${i}_${j}`}
                                                                name={`contentBlocks[${i}].content.${blockField.slug}`}
                                                                children={(field) => {
                                                                    console.log('Passing to InputRenderer:', {
                                                                        blockField,
                                                                        contentBlockTypes,
                                                                    });
                                                                    return (
                                                                        <InputRenderer
                                                                            blockField={blockField}
                                                                            field={field}
                                                                            filePreview={filePreview}
                                                                            form={form}
                                                                            contentBlockTypes={contentBlockTypes}
                                                                            currentName={`contentBlocks[${i}].content.${blockField.slug}`}
                                                                            i={i}
                                                                            j={j}
                                                                        />
                                                                    );
                                                                }}
                                                            />
                                                        ))}
                                                    </CardContent>
                                                    <CardFooter className="flex gap-2">
                                                        <Button variant="destructive" size="sm" type="button" onClick={() => fields.removeValue(i)}>
                                                            Remove <Trash className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm" type="button" onClick={() => fields.moveValue(i, i - 1)}>
                                                            Move Up <ChevronUp className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm" type="button" onClick={() => fields.moveValue(i, i + 1)}>
                                                            Move Down <ChevronDown className="h-4 w-4" />
                                                        </Button>
                                                    </CardFooter>
                                                </Card>
                                            );
                                        })
                                    )}
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {sortedContentBlockTypes.map((blockOption) => (
                                        <Button
                                            key={blockOption.id}
                                            variant="outline"
                                            size="sm"
                                            type="button"
                                            onClick={() =>
                                                // All fields need the type for the input reference and the content_block_type_id for the reference
                                                fields.pushValue({ content_block_type_id: blockOption.id.toString(), content: {} })
                                            }
                                        >
                                            {blockOption.name} <Plus className="h-4 w-4" />
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        )}
                    />
                    <div className="flex items-center gap-4">
                        <form.AppForm>
                            <form.SubscribeButton label={mode === 'create' ? 'Create Page' : 'Update Page'} />
                        </form.AppForm>

                        <Transition
                            show={form.state.isSubmitSuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-muted-foreground text-sm">Saved</p>
                        </Transition>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
