import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { ThirdPartyProvider } from '@/types';
import { Head } from '@inertiajs/react';
import Form from './third-party-provider-form';

interface Props {
    provider: ThirdPartyProvider;
}

export default function Edit({ provider }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit ${provider.name}`} />
            <PageContainer
                heading={`Edit ${provider.name}`}
                subheading="Edit the details of your provider"
                backUrl={route('system.third-party-providers.index')}
            >
                <Form
                    provider={provider}
                    defaultValues={{
                        name: provider.name,
                        description: provider.description ?? '',
                        is_active: provider.is_active,
                    }}
                    mode="edit"
                />
            </PageContainer>
        </AppLayout>
    );
}
