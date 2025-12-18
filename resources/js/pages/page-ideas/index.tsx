import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { PageIdea } from '@/types/models';
import { Head, Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { Eye, MessageSquare, Plus, Sparkles } from 'lucide-react';

interface Props {
    pageIdeas: PageIdea[];
}

export default function Index({ pageIdeas }: Props) {
    return (
        <AppLayout>
            <Head title="Page Ideas" />
            <PageContainer
                heading="Page Ideas"
                subheading="Manage your AI-generated landing page ideas"
                actionButton={
                    <Button asChild>
                        <Link href={route('page-ideas.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Generate New Idea
                        </Link>
                    </Button>
                }
            >
                {pageIdeas.length > 0 ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>All Page Ideas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Title</TableHead>
                                        <TableHead>Summary</TableHead>
                                        <TableHead>Conversation</TableHead>
                                        <TableHead>Version</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {pageIdeas.map((pageIdea) => (
                                        <TableRow key={pageIdea.id}>
                                            <TableCell className="font-medium">{pageIdea.title}</TableCell>
                                            <TableCell className="max-w-sm truncate">{pageIdea.summary}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <MessageSquare className="text-muted-foreground h-4 w-4" />
                                                    <span className="text-sm">{pageIdea.conversation.title}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Badge variant={pageIdea.is_latest_version ? 'default' : 'outline'}>
                                                        <Sparkles className="mr-1 h-3 w-3" />v{pageIdea.version_number}
                                                    </Badge>
                                                    {pageIdea.is_latest_version && (
                                                        <Badge variant="secondary" className="text-xs">
                                                            Latest
                                                        </Badge>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>{formatDistanceToNow(new Date(pageIdea.created_at), { addSuffix: true })}</TableCell>
                                            <TableCell className="text-right">
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link href={route('page-ideas.show', pageIdea.id)}>
                                                        <Eye className="h-4 w-4" />
                                                        <span className="sr-only">View</span>
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                ) : (
                    <NewItemCard heading="No page ideas yet" href={route('page-ideas.create')} buttonText="Generate Your First Idea" />
                )}
            </PageContainer>
        </AppLayout>
    );
}
