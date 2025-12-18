import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import ThirdPartyProviderableEditForm from '@/pages/third-parties/third-party-providerable-edit-form';
import { ThirdPartyProviderable } from '@/types/models/third-party-providerable';
import { Head } from '@inertiajs/react';

type Props = ThirdPartyProviderable;

export default function Edit({ provider, currentValues }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit ${provider.display_name} Configuration`} />
            <PageContainer
                heading={`Edit ${provider.display_name}`}
                subheading="Update your third-party service configuration"
                backUrl={route('third-parties.index')}
            >
                <ThirdPartyProviderableEditForm provider={provider} currentValues={currentValues} />
            </PageContainer>
        </AppLayout>
    );
}
