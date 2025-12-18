import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import AppLayout from '@/layouts/app-layout';
import type { ContentBlockType, Organisation } from '@/types/models';
import { Head, Link } from '@inertiajs/react';
import { Blocks, Eye, PlusCircle, Tag } from 'lucide-react';

interface ContentBlockTypesPageProps {
    contentBlockTypes: ContentBlockType[];
    organisation: Organisation;
}

export default function ContentBlockTypesIndex({ contentBlockTypes, organisation }: ContentBlockTypesPageProps) {
    return (
        <AppLayout>
            <Head title="Content Block Types" />
            <PageContainer heading={`${organisation.name} - Content Block Types`} subheading="Manage your content block types">
                {contentBlockTypes?.length === 0 ? (
                    <Card className="border-dashed">
                        <CardHeader className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle>No content block types yet</CardTitle>
                                <CardDescription>Get started by creating your first content block type</CardDescription>
                            </div>
                            <Button className="mt-4 w-full sm:mt-0 sm:w-auto">
                                <PlusCircle className="mr-2 h-4 w-4" />
                                <Link data-testid="create-content-block-type-button" href={route('content-block-types.create')}>
                                    Create Content Block Type
                                </Link>
                            </Button>
                        </CardHeader>
                    </Card>
                ) : (
                    <div data-testid="content-block-types-grid" className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {contentBlockTypes?.map((type) => (
                            <Card
                                key={type.id}
                                data-testid={`content-block-type-${type.id}`}
                                className="group relative transition-all hover:shadow-lg"
                            >
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <CardTitle data-testid={`content-block-type-${type.id}-name`} className="flex items-center">
                                                <Blocks className="text-primary mr-2 h-5 w-5" />
                                                {type.name}
                                            </CardTitle>
                                            <CardDescription data-testid={`content-block-type-${type.id}-slug`} className="mt-1 flex items-center">
                                                <Tag className="text-muted-foreground mr-2 h-4 w-4" />
                                                {type.slug}
                                            </CardDescription>
                                        </div>
                                        <span
                                            data-testid={`content-block-type-${type.id}-type-badge`}
                                            className={`rounded-full px-3 py-1 text-xs font-medium ${
                                                type.is_default ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'
                                            }`}
                                        >
                                            {type.is_default ? 'Default' : 'Custom'}
                                        </span>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground mb-2 text-sm font-medium">Fields:</p>
                                    <ul data-testid={`content-block-type-${type.id}-fields`} className="space-y-2">
                                        {type.fields?.map((field) => (
                                            <li
                                                key={field.label}
                                                data-testid={`content-block-type-${type.id}-field-${field.label}`}
                                                className="bg-muted/50 flex items-center rounded-md px-3 py-2 text-sm"
                                            >
                                                <span className="font-medium">{field.label}</span>
                                                <span className="bg-primary/10 text-primary ml-auto rounded-full px-2 py-0.5 text-xs font-medium">
                                                    {field.type}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                                <CardFooter>
                                    <Button variant="outline" asChild>
                                        <Link
                                            data-testid={`content-block-type-${type.id}-view-details`}
                                            href={route('content-block-types.show', type.slug)}
                                        >
                                            <Eye className="mr-2 h-4 w-4" />
                                            View Details
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                        <NewItemCard
                            heading="Add a new content block type"
                            href={route('content-block-types.create')}
                            buttonText="Create Content Block Type"
                        />
                    </div>
                )}
            </PageContainer>
        </AppLayout>
    );
}
