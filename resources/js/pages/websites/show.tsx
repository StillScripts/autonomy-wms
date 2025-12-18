import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { GlobalContentBlock, Page, Website } from '@/types/models';
import { Head, Link, router } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { BookHeart, Globe, Pencil, Plus, Trash } from 'lucide-react';

interface WebsitesPageProps {
    website: Website;
    pages: Page[];
    globalContentBlocks: GlobalContentBlock[];
}

export default function WebsitesShow({ website, pages, globalContentBlocks }: WebsitesPageProps) {
    return (
        <AppLayout>
            <Head title="View Website" />
            <PageContainer
                backUrl={route('websites.index')}
                heading={`View Website`}
                subheading={`View the pages for ${website.title}`}
                actionButton={
                    <Button asChild>
                        <Link href={route('websites.edit', website.id)}>
                            <Pencil className="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                }
            >
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BookHeart className="h-5 w-5" />
                                Pages
                            </CardTitle>
                            <CardDescription>Manage the pages for {website.title}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Title</TableHead>
                                        <TableHead>Slug</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Created At</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {pages.map((page) => (
                                        <TableRow key={page.id}>
                                            <TableCell>{page.title}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{page.slug}</Badge>
                                            </TableCell>
                                            <TableCell>
                                                <span className="line-clamp-2 truncate">{page.description}</span>
                                            </TableCell>
                                            <TableCell>{formatDistanceToNow(new Date(page.created_at), { addSuffix: true })}</TableCell>
                                            <TableCell className="flex gap-2">
                                                <Button variant="outline" size="icon">
                                                    <Link href={route('websites.pages.edit', { website: website.id, page: page.slug })}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <Button variant="outline" size="icon">
                                                            <Trash className="h-4 w-4" />
                                                            <span className="sr-only">Delete</span>
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>Delete Page</DialogTitle>
                                                            <DialogDescription>Are you sure you want to delete this page?</DialogDescription>
                                                        </DialogHeader>
                                                        <DialogFooter>
                                                            <DialogClose asChild>
                                                                <Button variant="outline">Cancel</Button>
                                                            </DialogClose>
                                                            <Button
                                                                variant="destructive"
                                                                onClick={() =>
                                                                    router.delete(
                                                                        route('websites.pages.destroy', {
                                                                            website: website.id,
                                                                            page: page.slug,
                                                                        }),
                                                                    )
                                                                }
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
                        <CardFooter>
                            <Button variant="outline" asChild>
                                <Link href={`/websites/${website.id}/pages/create`}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add New Page
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Globe className="h-5 w-5" />
                                Global Content Blocks
                            </CardTitle>
                            <CardDescription>Content blocks that appear across all pages of {website.title}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Created At</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {globalContentBlocks.length > 0 ? (
                                        globalContentBlocks.map((globalBlock) => (
                                            <TableRow key={globalBlock.id}>
                                                <TableCell>
                                                    <Badge variant="secondary">{globalBlock.content_block.block_type.name}</Badge>
                                                </TableCell>
                                                <TableCell className="max-w-xs">
                                                    <span className="line-clamp-2 truncate">{globalBlock.content_block.description}</span>
                                                </TableCell>
                                                <TableCell>{formatDistanceToNow(new Date(globalBlock.created_at), { addSuffix: true })}</TableCell>
                                                <TableCell className="flex gap-2">
                                                    <Button variant="outline" size="icon">
                                                        <Link href={route('content-blocks.edit', globalBlock.content_block.id)}>
                                                            <Pencil className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                    <Dialog>
                                                        <DialogTrigger asChild>
                                                            <Button variant="outline" size="icon">
                                                                <Trash className="h-4 w-4" />
                                                                <span className="sr-only">Remove Global</span>
                                                            </Button>
                                                        </DialogTrigger>
                                                        <DialogContent>
                                                            <DialogHeader>
                                                                <DialogTitle>Remove Global Content Block</DialogTitle>
                                                                <DialogDescription>
                                                                    Are you sure you want to remove this content block from global display? This will
                                                                    not delete the content block itself.
                                                                </DialogDescription>
                                                            </DialogHeader>
                                                            <DialogFooter>
                                                                <DialogClose asChild>
                                                                    <Button variant="outline">Cancel</Button>
                                                                </DialogClose>
                                                                <Button
                                                                    variant="destructive"
                                                                    onClick={() =>
                                                                        router.delete(
                                                                            route('websites.global-content-blocks.destroy', {
                                                                                website: website.id,
                                                                                globalContentBlock: globalBlock.id,
                                                                            }),
                                                                        )
                                                                    }
                                                                >
                                                                    Remove
                                                                </Button>
                                                            </DialogFooter>
                                                        </DialogContent>
                                                    </Dialog>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-muted-foreground py-8 text-center">
                                                No global content blocks assigned to this website yet.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                        <CardFooter>
                            <Button variant="outline" asChild>
                                <Link href={`/websites/${website.id}/global-content-blocks/edit`}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Manage Global Content Blocks
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
