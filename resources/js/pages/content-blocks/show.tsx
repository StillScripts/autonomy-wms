import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type PageProps } from '@/types';
import type { ContentBlock, ContentBlockType } from '@/types/models';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit } from 'lucide-react';

interface Props extends PageProps {
    contentBlock: ContentBlock & { block_type: ContentBlockType };
}

export default function Show({ contentBlock }: Props) {
    return (
        <AppLayout>
            <Head title={`View Content Block`} />
            <PageContainer heading={`View Content Block`} subheading={`View a content block`}>
                <div className="container py-6">
                    <div className="mb-6 flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <Button variant="ghost" size="icon" asChild>
                                <Link href={route('content-blocks.index')}>
                                    <ArrowLeft className="h-4 w-4" />
                                    <span className="sr-only">Back</span>
                                </Link>
                            </Button>
                            <h1 className="text-2xl font-bold tracking-tight">{contentBlock.block_type.name}</h1>
                        </div>
                        <Button asChild>
                            <Link href={route('content-blocks.edit', contentBlock.id)}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>

                    <div className="grid gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>General Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl className="grid gap-4">
                                    <div>
                                        <dt className="text-muted-foreground text-sm font-medium">Description</dt>
                                        <dd>{contentBlock.description}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground text-sm font-medium">Created</dt>
                                        <dd>{new Date(contentBlock.created_at).toLocaleString()}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground text-sm font-medium">Updated</dt>
                                        <dd>{new Date(contentBlock.updated_at).toLocaleString()}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Content</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {Object.entries(contentBlock.content_with_urls).map(([key, value]) => (
                                    <div key={key}>
                                        <dt className="text-muted-foreground text-sm font-medium">{key}</dt>
                                        <dd>
                                            {typeof value === 'object' ? (
                                                JSON.stringify(value)
                                            ) : value?.includes('s3.ap-southeast-2.amazonaws.com') ? (
                                                <img src={value} alt={key} />
                                            ) : (
                                                value
                                            )}
                                        </dd>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
