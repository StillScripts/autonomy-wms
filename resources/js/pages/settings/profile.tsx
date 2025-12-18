import { type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, router, usePage } from '@inertiajs/react';

import DeleteUser from '@/components/delete-user';
import { useAppForm } from '@/components/forms/form-context';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<SharedData>().props;

    const form = useAppForm({
        defaultValues: {
            name: auth.user.name,
            email: auth.user.email,
        },
        onSubmit: async (data) => {
            await new Promise((resolve) => setTimeout(resolve, 1000));
            router.patch(route('profile.update'), data.value);
        },
    });

    return (
        <AppLayout>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Profile information" description="Update your name and email address" />

                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            form.handleSubmit();
                        }}
                        className="space-y-6"
                    >
                        <form.AppField
                            name="name"
                            validators={{
                                onChange: ({ value }) =>
                                    !value ? 'A name is required' : value.length < 2 ? 'Name must be at least 2 characters' : undefined,
                            }}
                            children={(field) => {
                                return (
                                    <field.FormInput
                                        name={field.name}
                                        label="Full Name"
                                        required
                                        className="mt-1 block w-full"
                                        autoComplete="name"
                                        placeholder="Full Name"
                                    />
                                );
                            }}
                        />

                        <form.AppField
                            name="email"
                            validators={{
                                onChange: ({ value }) =>
                                    !value ? 'An email is required' : value.length < 2 ? 'Email must be at least 2 characters' : undefined,
                            }}
                            children={(field) => {
                                return (
                                    <field.FormInput
                                        label="Email address"
                                        id={field.name}
                                        className="mt-1 block w-full"
                                        name={field.name}
                                        required
                                        autoComplete="email"
                                        placeholder="Email address"
                                    />
                                );
                            }}
                        />

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="text-muted-foreground -mt-4 text-sm">
                                    Your email address is unverified.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        A new verification link has been sent to your email address.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex items-center gap-4">
                            <form.Subscribe
                                selector={(state) => [state.canSubmit, state.isSubmitting]}
                                children={([canSubmit, isSubmitting]) => (
                                    <Button type="submit" disabled={!canSubmit || isSubmitting}>
                                        Save
                                    </Button>
                                )}
                            />

                            <Transition
                                show={form.state.isSubmitSuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-muted-foreground text-sm">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
