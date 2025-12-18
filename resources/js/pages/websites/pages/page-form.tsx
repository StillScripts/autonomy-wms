import { useAppForm, type ReusableForm } from '@/components/forms/form-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import type { ContentBlock, ContentBlockType, Page as PageModel, Website } from '@/types/models';
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
    contentBlocks: ContentBlock[];
    page?: PageModel;
    defaultValues?: {
        title: string;
        description: string;
        contentBlocks: {
            content_block_type_id: string;
            content_block_id: string;
        }[];
    };
}

export function PageForm({
    mode,
    website,
    contentBlockTypes,
    contentBlocks,
    defaultValues = {
        title: '',
        description: '',
        contentBlocks: [] as { content_block_type_id: string; content_block_id: string }[],
    },
    page,
}: PageFormProps) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            if (mode === 'create') {
                router.post(route('websites.pages.store', website.id), data.value);
            } else {
                router.post(route('websites.pages.update', { website: website.id, page: page?.slug }), {
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
                                            const filteredContentBlocks = contentBlocks.filter(
                                                (c) => c.content_block_type_id.toString() == _.content_block_type_id,
                                            );
                                            return (
                                                <Card key={i}>
                                                    <CardHeader>
                                                        <CardTitle>{blockType?.name}</CardTitle>
                                                    </CardHeader>
                                                    <CardContent>
                                                        {filteredContentBlocks.length > 0 ? (
                                                            <form.AppField
                                                                name={`contentBlocks[${i}].content_block_id`}
                                                                children={(field) => {
                                                                    return (
                                                                        <field.FormRadioGroup
                                                                            name={field.name}
                                                                            label={`Select a ${blockType?.name} content block`}
                                                                            options={filteredContentBlocks.map((c) => ({
                                                                                value: c.id.toString(),
                                                                                label: c.description,
                                                                            }))}
                                                                        />
                                                                    );
                                                                }}
                                                            />
                                                        ) : (
                                                            <span className="text-muted-foreground text-sm">No content blocks found.</span>
                                                        )}
                                                    </CardContent>
                                                    <CardFooter className="flex gap-2">
                                                        <Dialog>
                                                            <DialogTrigger asChild>
                                                                <Button
                                                                    variant="destructive"
                                                                    size="sm"
                                                                    type="button"
                                                                    onClick={() => fields.removeValue(i)}
                                                                >
                                                                    Remove <Trash className="h-4 w-4" />
                                                                </Button>
                                                            </DialogTrigger>
                                                            <DialogContent>
                                                                <DialogHeader>
                                                                    <DialogTitle>Remove Content Block</DialogTitle>
                                                                    <DialogDescription>
                                                                        Are you sure you want to remove this content block?
                                                                    </DialogDescription>
                                                                </DialogHeader>
                                                                <DialogFooter>
                                                                    <DialogClose asChild>
                                                                        <Button variant="outline" type="button">
                                                                            Cancel
                                                                        </Button>
                                                                    </DialogClose>
                                                                    <Button variant="destructive" type="button" onClick={() => fields.removeValue(i)}>
                                                                        Remove
                                                                    </Button>
                                                                </DialogFooter>
                                                            </DialogContent>
                                                        </Dialog>

                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            type="button"
                                                            onClick={() => fields.moveValue(i, i - 1)}
                                                            disabled={i === 0}
                                                        >
                                                            Move Up <ChevronUp className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            type="button"
                                                            onClick={() => fields.moveValue(i, i + 1)}
                                                            disabled={i === fields.state.value.length - 1}
                                                        >
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
                                                fields.pushValue({ content_block_type_id: blockOption.id.toString(), content_block_id: '' })
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
