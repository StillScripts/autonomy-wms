import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import WebsitesForm from '@/pages/websites/website-form';
import type { Website } from '@/types/models';
import { Head } from '@inertiajs/react';

interface WebsitesEditProps {
    website: Website;
}

export default function WebsitesEdit({ website }: WebsitesEditProps) {
    return (
        <AppLayout>
            <Head title="Edit Website" />
            <PageContainer backUrl={route('websites.index')} heading={`Edit Website`} subheading={`Edit the details for ${website.title}`}>
                <WebsitesForm
                    mode="edit"
                    defaultValues={{
                        title: website.title,
                        domain: website.domain,
                        description: website.description || '',
                        logo: website.logo_url || null,
                    }}
                    websiteId={website.id}
                />
            </PageContainer>
        </AppLayout>
    );
}
