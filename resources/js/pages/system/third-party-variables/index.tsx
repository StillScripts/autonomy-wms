import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { ThirdPartyVariable } from '@/types/models';
import { Head, Link } from '@inertiajs/react';

interface Props {
    variables: ThirdPartyVariable[];
}

export default function Index({ variables }: Props) {
    return (
        <AppLayout>
            <Head title="Third Party Variables" />
            <PageContainer
                heading="Third Party Variables"
                subheading="Manage the third party variables"
                actionButton={
                    <Button asChild>
                        <Link href={route('system.third-party-variables.create')}>Add New Variable</Link>
                    </Button>
                }
            >
                {variables.length > 0 ? (
                    <Card>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Provider</TableHead>
                                        <TableHead>Secret</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {variables.map((variable) => (
                                        <TableRow key={variable.id}>
                                            <TableCell>{variable.name}</TableCell>
                                            <TableCell>{variable.description}</TableCell>
                                            <TableCell>{variable.provider?.name}</TableCell>
                                            <TableCell>
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        variable.is_secret ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'
                                                    }`}
                                                >
                                                    {variable.is_secret ? 'Secret' : 'Public'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link
                                                    href={route('system.third-party-variables.edit', variable.id)}
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
                    <NewItemCard heading="Add your first variable" href={route('system.third-party-variables.create')} buttonText="Create Variable" />
                )}
            </PageContainer>
        </AppLayout>
    );
}
