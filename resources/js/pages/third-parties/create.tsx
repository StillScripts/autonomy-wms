import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import ThirdPartyProviderableForm from '@/pages/third-parties/third-party-providerable-create-form';
import { ThirdPartyProviderableCreate } from '@/types/models/third-party-providerable-create';
import { Head } from '@inertiajs/react';

type Props = ThirdPartyProviderableCreate;

export default function Create({ availableProviders }: Props) {
    return (
        <AppLayout>
            <Head title="Add Third Party Provider" />
            <PageContainer
                heading="Add Third Party Provider"
                subheading="Configure a new third-party service integration"
                backUrl={route('third-parties.index')}
            >
                <ThirdPartyProviderableForm availableProviders={availableProviders} />
            </PageContainer>
        </AppLayout>
    );
}
