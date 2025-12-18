import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Page, Website } from '@/types/models';
import { Head, Link } from '@inertiajs/react';

interface PagesIndexProps {
    website: Website;
    pages: Page[];
}

export default function PagesIndex({ website, pages }: PagesIndexProps) {
    return (
        <AppLayout>
            <Head title={`Pages - ${website.title}`} />
            <PageContainer heading={`Pages for ${website.title}`} subheading="Manage the pages for this website">
                <Card>
                    <CardHeader>
                        <CardTitle>All Pages</CardTitle>
                        <CardDescription>A list of all pages for {website.title}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {pages.length === 0 ? (
                            <div className="p-4 text-center">
                                <p className="text-muted-foreground">No pages found.</p>
                                <Link href={route('websites.pages.create', website.id)}>
                                    <Button variant="outline" className="mt-4">
                                        Create Page
                                    </Button>
                                </Link>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {pages.map((page) => (
                                    <div key={page.id} className="flex items-center justify-between border-b pb-4 last:border-0">
                                        <div>
                                            <h3 className="font-semibold">{page.title}</h3>
                                            <p className="text-muted-foreground text-sm">{page.description || 'No description'}</p>
                                        </div>
                                        <Link href={route('websites.pages.show', [website.id, page.id])}>
                                            <Button variant="outline" size="sm">
                                                View
                                            </Button>
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </PageContainer>
        </AppLayout>
    );
}
