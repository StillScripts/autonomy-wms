import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import AppLayout from '@/layouts/app-layout';
import type { Organisation, Website } from '@/types/models';
import { Head, Link } from '@inertiajs/react';
import { Eye } from 'lucide-react';

interface WebsitesPageProps {
    websites: Website[];
    currentOrganisation: Organisation;
}

export default function WebsitesIndex({ websites, currentOrganisation }: WebsitesPageProps) {
    return (
        <AppLayout>
            <Head title="Websites" />
            <PageContainer heading={`${currentOrganisation?.name} - Websites`} subheading="Manage your websites and domains">
                {websites?.length === 0 ? (
                    <Card>
                        <CardHeader className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle>No websites yet</CardTitle>
                                <CardDescription>Get started by creating your first website</CardDescription>
                            </div>
                            <Button className="mt-4 w-full sm:mt-0 sm:w-auto">
                                <Link href={route('websites.create')}>Create Website</Link>
                            </Button>
                        </CardHeader>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {websites?.map((website) => (
                            <Card key={website.id}>
                                <CardHeader>
                                    <div className="flex items-center space-x-4">
                                        {website.logo_url ? (
                                            <img
                                                src={website.logo_url}
                                                alt={`${website.title} logo`}
                                                className="h-12 w-12 rounded-md object-contain"
                                            />
                                        ) : (
                                            <div className="flex h-12 w-12 items-center justify-center rounded-md bg-gray-100">
                                                <span className="text-sm text-gray-400">No logo</span>
                                            </div>
                                        )}
                                        <div>
                                            <CardTitle>{website.title}</CardTitle>
                                            <CardDescription>{website.domain}</CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>{website.description && <p className="text-sm text-gray-600">{website.description}</p>}</CardContent>

                                <CardFooter>
                                    <Button variant="outline" asChild>
                                        <Link href={route('websites.show', website.id)}>
                                            <Eye className="mr-2 h-4 w-4" />
                                            View Details
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                        <NewItemCard heading="Add a new website" href={route('websites.create')} buttonText="Create Website" />
                    </div>
                )}
            </PageContainer>
        </AppLayout>
    );
}
