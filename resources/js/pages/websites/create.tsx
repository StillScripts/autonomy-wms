import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import WebsitesForm from '@/pages/websites/website-form';
import { Organisation } from '@/types/models';
import { Head } from '@inertiajs/react';

interface WebsitesPageProps {
    organisation: Organisation;
}

export default function WebsitesCreate({ organisation }: WebsitesPageProps) {
    return (
        <AppLayout>
            <Head title="Create Website" />
            <PageContainer heading={`Create Website`} subheading={`Create a new website for ${organisation.name}`}>
                <WebsitesForm mode="create" />
            </PageContainer>
        </AppLayout>
    );
}
