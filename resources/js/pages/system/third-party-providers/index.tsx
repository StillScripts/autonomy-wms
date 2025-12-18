import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { ThirdPartyProvider } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Props {
    providers: ThirdPartyProvider[];
}

export default function Index({ providers }: Props) {
    return (
        <AppLayout>
            <Head title="Third Party Providers" />
            <PageContainer
                heading="Third Party Providers"
                subheading="Manage the third party providers"
                actionButton={
                    <Button asChild>
                        <Link href={route('system.third-party-providers.create')}>Add New Provider</Link>
                    </Button>
                }
            >
                {providers.length > 0 ? (
                    <Card>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {providers.map((provider) => (
                                        <TableRow key={provider.id}>
                                            <TableCell>{provider.name}</TableCell>
                                            <TableCell>{provider.description}</TableCell>
                                            <TableCell>
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        provider.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                                    }`}
                                                >
                                                    {provider.is_active ? 'Active' : 'Inactive'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link
                                                    href={route('system.third-party-providers.edit', provider.id)}
                                                    className="mr-4 text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                ) : (
                    <NewItemCard heading="Add your first provider" href={route('system.third-party-providers.create')} buttonText="Create Provider" />
                )}
            </PageContainer>
        </AppLayout>
    );
}
