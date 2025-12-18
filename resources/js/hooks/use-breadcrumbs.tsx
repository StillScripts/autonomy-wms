import { capitalize } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

export function useBreadcrumbs() {
    const currentRoute = route().current();
    const params = Object.fromEntries(Object.entries(route().params).map(([key, value]) => [key, capitalize(value.replace(/-/g, ' '), true)]));

    let breadcrumbs: BreadcrumbItem[] = [];

    switch (currentRoute) {
        // Dashboard
        case 'dashboard':
            breadcrumbs = [
                {
                    title: 'Dashboard',
                },
            ];
            break;

        // Websites
        case 'websites.index':
            breadcrumbs = [
                {
                    title: 'Websites',
                },
            ];
            break;
        case 'websites.show':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                },
            ];
            break;
        case 'websites.create':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: 'Create',
                },
            ];
            break;
        case 'websites.edit':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                    href: `/websites/${params.website}`,
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Website Pages (nested resource)
        case 'websites.pages.index':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                    href: `/websites/${params.website}`,
                },
                {
                    title: 'Pages',
                },
            ];
            break;
        case 'websites.pages.show':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                    href: `/websites/${params.website}`,
                },
                {
                    title: 'Pages',
                    href: `/websites/${params.website}/pages`,
                },
                {
                    title: params.page,
                },
            ];
            break;
        case 'websites.pages.create':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                    href: `/websites/${params.website}`,
                },
                {
                    title: 'Pages',
                    href: `/websites/${params.website}/pages`,
                },
                {
                    title: 'Create',
                },
            ];
            break;
        case 'websites.pages.edit':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                    href: `/websites/${params.website}`,
                },
                {
                    title: 'Pages',
                    href: `/websites/${params.website}/pages`,
                },
                {
                    title: params.page,
                    href: `/websites/${params.website}/pages/${params.page}`,
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Global Content Blocks (nested under websites)
        case 'websites.global-content-blocks.edit':
            breadcrumbs = [
                {
                    title: 'Websites',
                    href: '/websites',
                },
                {
                    title: params.website,
                    href: `/websites/${params.website}`,
                },
                {
                    title: 'Global Content Blocks',
                },
            ];
            break;

        // Content Block Types
        case 'content-block-types.index':
            breadcrumbs = [
                {
                    title: 'Content Block Types',
                },
            ];
            break;
        case 'content-block-types.show':
            breadcrumbs = [
                {
                    title: 'Content Block Types',
                    href: '/content-block-types',
                },
                {
                    title: params.content_block_type,
                },
            ];
            break;
        case 'content-block-types.create':
            breadcrumbs = [
                {
                    title: 'Content Block Types',
                    href: '/content-block-types',
                },
                {
                    title: 'Create',
                },
            ];
            break;
        case 'content-block-types.edit':
            breadcrumbs = [
                {
                    title: 'Content Block Types',
                    href: '/content-block-types',
                },
                {
                    title: params.content_block_type,
                    href: `/content-block-types/${params.content_block_type}`,
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Content Blocks
        case 'content-blocks.index':
            breadcrumbs = [
                {
                    title: 'Content Blocks',
                },
            ];
            break;
        case 'content-blocks.show':
            breadcrumbs = [
                {
                    title: 'Content Blocks',
                    href: '/content-blocks',
                },
                {
                    title: params.content_block,
                },
            ];
            break;
        case 'content-blocks.create':
            breadcrumbs = [
                {
                    title: 'Content Blocks',
                    href: '/content-blocks',
                },
                {
                    title: 'Create',
                },
            ];
            break;
        case 'content-blocks.edit':
            breadcrumbs = [
                {
                    title: 'Content Blocks',
                    href: '/content-blocks',
                },
                {
                    title: params.content_block,
                    href: `/content-blocks/${params.content_block}`,
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Private Files
        case 'private-files.index':
            breadcrumbs = [
                {
                    title: 'Private Files',
                },
            ];
            break;
        case 'private-files.show':
            breadcrumbs = [
                {
                    title: 'Private Files',
                    href: '/private-files',
                },
                {
                    title: params.private_file,
                },
            ];
            break;
        case 'private-files.create':
            breadcrumbs = [
                {
                    title: 'Private Files',
                    href: '/private-files',
                },
                {
                    title: 'Create',
                },
            ];
            break;

        // Third Party Integrations
        case 'third-parties.index':
            breadcrumbs = [
                {
                    title: 'Third Party Integrations',
                },
            ];
            break;
        case 'third-parties.create':
            breadcrumbs = [
                {
                    title: 'Third Party Integrations',
                    href: '/third-parties',
                },
                {
                    title: 'Create',
                },
            ];
            break;
        case 'third-parties.edit':
            breadcrumbs = [
                {
                    title: 'Third Party Integrations',
                    href: '/third-parties',
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Conversations
        case 'conversations.index':
            breadcrumbs = [
                {
                    title: 'Conversations',
                },
            ];
            break;
        case 'conversations.show':
            breadcrumbs = [
                {
                    title: 'Conversations',
                    href: '/conversations',
                },
                {
                    title: params.conversation,
                },
            ];
            break;
        case 'conversations.create':
            breadcrumbs = [
                {
                    title: 'Conversations',
                    href: '/conversations',
                },
                {
                    title: 'Create',
                },
            ];
            break;
        case 'conversations.edit':
            breadcrumbs = [
                {
                    title: 'Conversations',
                    href: '/conversations',
                },
                {
                    title: params.conversation,
                    href: `/conversations/${params.conversation}`,
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Products
        case 'products.index':
            breadcrumbs = [
                {
                    title: 'Products',
                },
            ];
            break;
        case 'products.edit':
            breadcrumbs = [
                {
                    title: 'Products',
                    href: '/products',
                },
                {
                    title: params.product,
                },
                {
                    title: 'Edit',
                },
            ];
            break;

        // Payments
        case 'payments.index':
            breadcrumbs = [
                {
                    title: 'Payments',
                },
            ];
            break;

        // Customers
        case 'customers.index':
            breadcrumbs = [
                {
                    title: 'Customers',
                },
            ];
            break;

        // System Settings
        case 'system.index':
            breadcrumbs = [
                {
                    title: 'System Settings',
                },
            ];
            break;

        // Home/Welcome
        case 'home':
            breadcrumbs = [
                {
                    title: 'Home',
                },
            ];
            break;

        default:
            // Fallback for unknown routes
            breadcrumbs = [
                {
                    title: 'Dashboard',
                    href: '/dashboard',
                },
            ];
            break;
    }

    return breadcrumbs;
}
