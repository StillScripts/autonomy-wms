import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { money } from '@/lib/utils';
import { Customer } from '@/types/models';
import { Head } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

type Props = {
    customers: {
        data: Customer[];
    };
};

export default function Index({ customers }: Props) {
    const getTotalSpent = (products: Customer['products']) => {
        if (products.length === 0) return 0;
        return products.reduce((total, product) => total + parseFloat(product.price.toString()), 0);
    };

    const getProductCount = (products: Customer['products']) => {
        return products.length;
    };

    return (
        <AppLayout>
            <Head title="Customers" />
            <PageContainer heading="Customers" subheading="View your customers">
                {customers.data.length > 0 ? (
                    <Card>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Products Owned</TableHead>
                                        <TableHead>Total Spent</TableHead>
                                        <TableHead>Customer Since</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {customers.data.map((customer) => (
                                        <TableRow key={customer.id}>
                                            <TableCell className="font-medium">{customer.name}</TableCell>
                                            <TableCell>{customer.email}</TableCell>
                                            <TableCell>
                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <Badge variant="secondary" className="cursor-pointer">
                                                            {getProductCount(customer.products)}{' '}
                                                            {getProductCount(customer.products) === 1 ? 'product' : 'products'}
                                                        </Badge>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>Purchases for {customer.name}</DialogTitle>
                                                            <DialogDescription>A list of all products purchased by this customer.</DialogDescription>
                                                        </DialogHeader>
                                                        {customer.products.length > 0 ? (
                                                            <Table>
                                                                <TableHeader>
                                                                    <TableRow>
                                                                        <TableHead>Product Name</TableHead>
                                                                        <TableHead>Price</TableHead>
                                                                        <TableHead>Time of Purchase</TableHead>
                                                                    </TableRow>
                                                                </TableHeader>
                                                                <TableBody>
                                                                    {customer.products.map((product) => (
                                                                        <TableRow key={product.id}>
                                                                            <TableCell>{product.name}</TableCell>
                                                                            <TableCell>{money(product.price)}</TableCell>
                                                                            <TableCell>
                                                                                {product.pivot?.created_at
                                                                                    ? formatDistanceToNow(new Date(product.pivot.created_at), {
                                                                                          addSuffix: true,
                                                                                      })
                                                                                    : '-'}
                                                                            </TableCell>
                                                                        </TableRow>
                                                                    ))}
                                                                </TableBody>
                                                            </Table>
                                                        ) : (
                                                            <p className="py-4 text-center text-gray-500">No products found for this customer.</p>
                                                        )}
                                                        <DialogClose asChild>
                                                            <Button variant="ghost" className="mt-4">
                                                                Close
                                                            </Button>
                                                        </DialogClose>
                                                    </DialogContent>
                                                </Dialog>
                                            </TableCell>
                                            <TableCell>{money(getTotalSpent(customer.products))}</TableCell>
                                            <TableCell>{new Date(customer.created_at).toLocaleDateString()}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardContent className="py-8 text-center">
                            <p className="text-gray-500">No customers found</p>
                        </CardContent>
                    </Card>
                )}
            </PageContainer>
        </AppLayout>
    );
}
