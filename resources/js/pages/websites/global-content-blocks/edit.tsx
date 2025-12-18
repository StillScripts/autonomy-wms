import { useAppForm } from '@/components/forms/form-context';
import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
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
import AppLayout from '@/layouts/app-layout';
import { type PageProps } from '@/types';
import type { ContentBlock, ContentBlockType, Website } from '@/types/models';
import { Head, router } from '@inertiajs/react';
import { type } from 'arktype';
import { Plus, Trash } from 'lucide-react';

interface Props extends PageProps {
    website: Website;
    contentBlockTypes: ContentBlockType[];
    contentBlocks: ContentBlock[];
    globalContentBlocks: Array<{
        content_block_type_id: string;
        content_block_id: string;
    }>;
}

const GlobalContentBlocksSchema = type({
    globalContentBlocks: 'unknown[]',
});

type GlobalContentBlocksSchema = typeof GlobalContentBlocksSchema.infer;

export default function EditGlobalContentBlocks({ website, contentBlockTypes, contentBlocks, globalContentBlocks }: Props) {
    const form = useAppForm({
        defaultValues: {
            globalContentBlocks: globalContentBlocks.length > 0 ? globalContentBlocks : [],
        },
        onSubmit: async (data) => {
            router.put(route('websites.global-content-blocks.update', website.id), data.value);
        },
        validators: {
            onChange: GlobalContentBlocksSchema,
        },
    });

    const sortedContentBlockTypes = contentBlockTypes.sort((a, b) => a.name.localeCompare(b.name));

    return (
        <AppLayout>
            <Head title={`Manage Global Content Blocks - ${website.title}`} />
            <PageContainer
                heading="Manage Global Content Blocks"
                subheading={`Configure which content blocks appear globally across all pages of ${website.title}`}
                backUrl={route('websites.show', website.id)}
            >
                <Card>
                    <CardHeader>
                        <CardTitle>Global Content Blocks</CardTitle>
                        <CardDescription>
                            Add content blocks that will appear on all pages of your website. These blocks will be displayed in addition to any
                            page-specific content blocks.
                        </CardDescription>
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
                            <Separator />
                            <form.Field
                                name="globalContentBlocks"
                                mode="array"
                                children={(fields) => (
                                    <div className="space-y-4">
                                        <h4 className="text-lg font-medium">Global Content Blocks</h4>
                                        <div className="space-y-4">
                                            {!fields.state.value.length ? (
                                                <div className="text-muted-foreground py-8 text-center">
                                                    <p>No global content blocks configured yet.</p>
                                                    <p className="text-sm">Click one of the content block types below to get started.</p>
                                                </div>
                                            ) : (
                                                fields.state.value.map((_, i) => {
                                                    const blockType = contentBlockTypes.find((c) => c.id.toString() == _.content_block_type_id);
                                                    const filteredContentBlocks = contentBlocks.filter(
                                                        (c) => c.content_block_type_id.toString() == _.content_block_type_id,
                                                    );
                                                    return (
                                                        <Card key={i}>
                                                            <CardHeader>
                                                                <CardTitle>{blockType?.name || 'Unknown Block Type'}</CardTitle>
                                                            </CardHeader>
                                                            <CardContent>
                                                                {filteredContentBlocks.length > 0 ? (
                                                                    <form.AppField
                                                                        name={`globalContentBlocks[${i}].content_block_id`}
                                                                        children={(field) => {
                                                                            return (
                                                                                <field.FormRadioGroup
                                                                                    name={field.name}
                                                                                    label={`Select a ${blockType?.name} content block`}
                                                                                    options={filteredContentBlocks.map((c) => ({
                                                                                        value: c.id.toString(),
                                                                                        label: c.description || `Content Block #${c.id}`,
                                                                                    }))}
                                                                                />
                                                                            );
                                                                        }}
                                                                    />
                                                                ) : (
                                                                    <span className="text-muted-foreground text-sm">
                                                                        No content blocks found for this type.
                                                                    </span>
                                                                )}
                                                            </CardContent>
                                                            <CardFooter className="flex gap-2">
                                                                <Dialog>
                                                                    <DialogTrigger asChild>
                                                                        <Button variant="destructive" size="sm" type="button">
                                                                            Remove <Trash className="h-4 w-4" />
                                                                        </Button>
                                                                    </DialogTrigger>
                                                                    <DialogContent>
                                                                        <DialogHeader>
                                                                            <DialogTitle>Remove Global Content Block</DialogTitle>
                                                                            <DialogDescription>
                                                                                Are you sure you want to remove this global content block?
                                                                            </DialogDescription>
                                                                        </DialogHeader>
                                                                        <DialogFooter>
                                                                            <DialogClose asChild>
                                                                                <Button variant="outline" type="button">
                                                                                    Cancel
                                                                                </Button>
                                                                            </DialogClose>
                                                                            <Button
                                                                                variant="destructive"
                                                                                type="button"
                                                                                onClick={() => fields.removeValue(i)}
                                                                            >
                                                                                Remove
                                                                            </Button>
                                                                        </DialogFooter>
                                                                    </DialogContent>
                                                                </Dialog>
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
                                                        fields.pushValue({
                                                            content_block_type_id: blockOption.id.toString(),
                                                            content_block_id: '',
                                                        })
                                                    }
                                                >
                                                    {blockOption.name} <Plus className="h-4 w-4" />
                                                </Button>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            />
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => router.visit(route('websites.show', website.id))}>
                                    Cancel
                                </Button>
                                <form.AppForm>
                                    <form.SubscribeButton label="Save Global Content Blocks" />
                                </form.AppForm>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </PageContainer>
        </AppLayout>
    );
}
