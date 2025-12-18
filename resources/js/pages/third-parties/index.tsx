import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { ThirdPartyTab, ThirdPartyTabs } from '@/pages/third-parties/third-party-tabs';
import { ThirdPartyProviderableIndex } from '@/types/models/third-party-providerable-index';
import { Head, Link, router } from '@inertiajs/react';
import { Trash } from 'lucide-react';

type Props = ThirdPartyProviderableIndex;

const VariablesList = ({ config, tab }: { config: ThirdPartyProviderableIndex['providerConfigurations'][number]; tab: ThirdPartyTab }) => {
    return (
        <div className="space-y-2">
            {config.variables
                .filter((variable) => {
                    return tab === 'test'
                        ? config.provider.variables[variable.variable_key]?.is_test
                        : !config.provider.variables[variable.variable_key]?.is_test;
                })
                .map((variable) => (
                    <div key={variable.variable_key} className="flex items-center justify-between">
                        <span className="text-sm font-medium">{config.provider.variables[variable.variable_key]?.name || variable.variable_key}</span>
                        <span className="text-muted-foreground text-xs">
                            {variable.value
                                ? config.provider.variables[variable.variable_key]?.is_secret
                                    ? variable.value.replace(/./g, '*')
                                    : variable.value
                                : 'Not set'}
                        </span>
                    </div>
                ))}
        </div>
    );
};

export default function Index({ providerConfigurations }: Props) {
    const handleDeleteProvider = (providerValue: string) => {
        router.delete(`${route('third-parties.destroy')}?provider=${providerValue}`);
    };

    return (
        <AppLayout>
            <Head title="Third Party Providers" />
            <PageContainer
                heading="Third Party Providers"
                subheading="Manage your third-party service integrations"
                actionButton={
                    <Link href={route('third-parties.create')}>
                        <Button>Add Provider</Button>
                    </Link>
                }
            >
                <div className="grid gap-6">
                    {providerConfigurations.length === 0 ? (
                        <Card>
                            <CardContent className="pt-6">
                                <div className="text-center">
                                    <p className="text-muted-foreground mb-4">No third-party providers configured yet.</p>
                                    <Link href={route('third-parties.create')}>
                                        <Button>Add Your First Provider</Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        providerConfigurations.map((config) => (
                            <Card key={config.provider.value}>
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between">
                                        <span>{config.provider_name}</span>
                                        <div className="flex gap-2">
                                            <Link href={`${route('third-parties.edit')}?provider=${config.provider.value}`}>
                                                <Button variant="outline" size="sm">
                                                    Edit
                                                </Button>
                                            </Link>
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button variant="destructive" size="sm">
                                                        <Trash className="h-4 w-4" />
                                                        <span className="sr-only">Remove</span>
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Remove Provider Configuration</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to remove this {config.provider_name} configuration?
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <DialogFooter>
                                                        <DialogClose asChild>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button variant="destructive" onClick={() => handleDeleteProvider(config.provider.value)}>
                                                            Remove
                                                        </Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </CardTitle>
                                    <CardDescription>
                                        {config.variable_count} variable{config.variable_count !== 1 ? 's' : ''} configured
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <ThirdPartyTabs
                                        testContent={<VariablesList config={config} tab="test" />}
                                        liveContent={<VariablesList config={config} tab="live" />}
                                    />
                                </CardContent>
                            </Card>
                        ))
                    )}
                </div>
            </PageContainer>
        </AppLayout>
    );
}
