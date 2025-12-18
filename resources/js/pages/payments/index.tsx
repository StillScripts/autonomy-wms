import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { money } from '@/lib/utils';
import { Head } from '@inertiajs/react';

type Payment = {
    id: number;
    status: string;
    amount: number;
    currency: string;
    created_at: string;
    product: {
        name: string;
    };
    stripe_payment?: {
        stripe_payment_intent_id: string;
    };
};

type Props = {
    payments: {
        data: Payment[];
    };
};

export default function Index({ payments }: Props) {
    const getStatusBadge = (status: string) => {
        const variants = {
            pending: 'secondary',
            completed: 'default',
            failed: 'destructive',
            refunded: 'outline',
        } as const;

        return <Badge variant={variants[status as keyof typeof variants] || 'default'}>{status.charAt(0).toUpperCase() + status.slice(1)}</Badge>;
    };

    return (
        <AppLayout>
            <Head title="Payments" />
            <PageContainer heading="Payments" subheading="View your payment history">
                {payments.data.length > 0 ? (
                    <Card>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Payment ID</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {payments.data.map((payment) => (
                                        <TableRow key={payment.id}>
                                            <TableCell>{new Date(payment.created_at).toLocaleDateString()}</TableCell>
                                            <TableCell>{payment.product.name}</TableCell>
                                            <TableCell>{money(payment.amount)}</TableCell>
                                            <TableCell>{getStatusBadge(payment.status)}</TableCell>
                                            <TableCell className="font-mono text-sm">{payment.stripe_payment?.stripe_payment_intent_id}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardContent className="py-8 text-center">
                            <p className="text-gray-500">No payments found</p>
                        </CardContent>
                    </Card>
                )}
            </PageContainer>
        </AppLayout>
    );
}
