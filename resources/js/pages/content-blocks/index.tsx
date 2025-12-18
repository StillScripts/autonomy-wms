import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { Eye, Pencil, Trash } from 'lucide-react';

interface ContentBlock {
    id: number;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    content: Record<string, any>;
    created_at: string;
    updated_at: string;
    description: string;
    block_type: {
        id: number;
        name: string;
    };
    page: {
        id: number;
        title: string;
        website: {
            id: number;
            name: string;
        };
    };
}

interface Props extends PageProps {
    contentBlocks: ContentBlock[];
}

export default function Index({ contentBlocks }: Props) {
    return (
        <AppLayout>
            <Head title={`Create Content Block`} />
            <PageContainer
                heading={`Content Blocks`}
                subheading={`View your organisation's content blocks`}
                actionButton={
                    <Button asChild>
                        <Link href={route('content-blocks.create')}>Create Content Block</Link>
                    </Button>
                }
            >
                <Card>
                    <CardHeader>
                        <CardTitle>All Content Blocks</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Last Updated</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {contentBlocks.map((block) => (
                                    <TableRow key={block.id}>
                                        <TableCell className="font-medium">
                                            <Badge variant="default">{block.block_type.name}</Badge>
                                        </TableCell>
                                        <TableCell className="max-w-sm truncate">{block.description}</TableCell>
                                        <TableCell>{formatDistanceToNow(new Date(block.updated_at), { addSuffix: true })}</TableCell>
                                        <TableCell className="text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('content-blocks.show', block.id)}>
                                                    <Eye className="h-4 w-4" />
                                                    <span className="sr-only">View</span>
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('content-blocks.edit', block.id)}>
                                                    <Pencil className="h-4 w-4" />
                                                    <span className="sr-only">Edit</span>
                                                </Link>
                                            </Button>
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button variant="ghost" size="sm">
                                                        <Trash className="h-4 w-4" />
                                                        <span className="sr-only">Delete</span>
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Delete Content Block</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to delete this {block.block_type.name} content block?
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <DialogFooter>
                                                        <DialogClose asChild>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button
                                                            variant="destructive"
                                                            onClick={() => router.delete(route('content-blocks.destroy', block.id))}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </PageContainer>
        </AppLayout>
    );
}
