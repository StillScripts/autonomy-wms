import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { isTest } from '@/lib/stripe';
import { money } from '@/lib/utils';
import type { ProductIndex } from '@/types/models/product-index';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, ShieldCheck, TestTubeDiagonal } from 'lucide-react';

type Props = { products: ProductIndex };

export default function Index({ products }: Props) {
    const handleSync = () => {
        router.post(route('products.sync'));
    };

    const handleTestCheckout = (productId: number) => {
        router.post(
            route('products.test-stripe-checkout', productId),
            {},
            {
                onSuccess: (page) => {
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    if ((page.props as any).stripe_checkout_url) {
                        // eslint-disable-next-line @typescript-eslint/no-explicit-any
                        window.open((page.props as any).stripe_checkout_url, '_blank');
                    } else {
                        console.log('No stripe_checkout_url found in props:', page.props);
                    }
                },
                onError: (errors) => {
                    console.log('Inertia onError:', errors);
                    alert(errors.error || 'Failed to start test checkout');
                },
            },
        );
    };

    return (
        <AppLayout>
            <Head title="Products" />
            <PageContainer
                heading="Products"
                subheading="Manage your organisation's products"
                actionButton={
                    <Button onClick={handleSync} variant="outline">
                        Sync with Stripe
                    </Button>
                }
            >
                {products.data.length > 0 ? (
                    <Card>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Stripe ID</TableHead>
                                        <TableHead>Stripe Environment</TableHead>
                                        <TableHead>Product Types</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {products.data.map((product) => (
                                        <TableRow key={product.id}>
                                            <TableCell>{product.name}</TableCell>
                                            <TableCell className="max-w-sm truncate">{product.description}</TableCell>
                                            <TableCell>{money(product.price)}</TableCell>
                                            <TableCell>
                                                <Button variant="link" size="sm" asChild>
                                                    <a
                                                        href={`https://dashboard.stripe.com/${isTest(product) ? 'test/' : ''}products/${product.stripe_product?.stripe_id}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        {product.stripe_product?.stripe_id}
                                                    </a>
                                                </Button>
                                            </TableCell>
                                            <TableCell>
                                                {isTest(product) ? (
                                                    <Badge variant="outline">
                                                        <TestTubeDiagonal className="mr-2 h-4 w-4" />
                                                        Test
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="default">
                                                        <ShieldCheck className="mr-2 h-4 w-4" />
                                                        Live
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {product.product_types && product.product_types.length > 0 ? (
                                                    product.product_types.map((type) => (
                                                        <span
                                                            key={type.id}
                                                            className="mr-1 mb-1 inline-block rounded bg-neutral-800 px-2 py-0.5 text-xs text-neutral-100"
                                                        >
                                                            {type.name}
                                                        </span>
                                                    ))
                                                ) : (
                                                    <span className="text-muted-foreground text-xs">None</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="flex items-center justify-end gap-2 text-right">
                                                {product.stripe_product.stripe_environment === 'test' && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleTestCheckout(product.id)}
                                                        className="mr-2"
                                                    >
                                                        Test Payment
                                                    </Button>
                                                )}
                                                <Button variant="outline" size="icon" asChild>
                                                    <Link href={route('products.edit', product.id)}>
                                                        <Pencil className="h-4 w-4" />
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
                    <NewItemCard heading="No products yet" onClick={handleSync} buttonText="Sync with Stripe" />
                )}
            </PageContainer>
        </AppLayout>
    );
}
