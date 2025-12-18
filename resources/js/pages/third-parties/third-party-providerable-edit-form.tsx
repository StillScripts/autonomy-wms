import { useAppForm } from '@/components/forms/form-context';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { ThirdPartyTab, ThirdPartyTabs } from '@/pages/third-parties/third-party-tabs';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface Provider {
    value: string;
    display_name: string;
    variables: Record<
        string,
        {
            name: string;
            description: string;
            is_secret: boolean;
            is_test: boolean;
        }
    >;
}

interface Props {
    provider: Provider;
    currentValues: Record<string, string>;
}

const VariablesFormInputs = ({
    provider,
    variables,
    tab,
    handleVariableChange,
}: {
    provider: Provider;
    variables: Record<string, string>;
    tab: ThirdPartyTab;
    handleVariableChange: (key: string, value: string) => void;
}) => {
    return (
        <div className="space-y-4">
            {Object.entries(provider.variables)
                .filter(([, config]) => (tab === 'test' ? config.is_test : !config.is_test))
                .map(([key, config]) => (
                    <div key={key} className="space-y-2">
                        <label htmlFor={key} className="text-sm font-medium">
                            {config.name}
                        </label>
                        {config.description && <p className="text-muted-foreground text-xs">{config.description}</p>}
                        <input
                            id={key}
                            type={config.is_secret ? 'password' : 'text'}
                            value={variables[key] || ''}
                            onChange={(e) => handleVariableChange(key, e.target.value)}
                            className="border-input w-full rounded-md border px-3 py-2"
                        />
                    </div>
                ))}
        </div>
    );
};

/**
 * Configure the variables for a third party provider
 */
export default function EditForm({ provider, currentValues }: Props) {
    const [variables, setVariables] = useState<Record<string, string>>(currentValues);

    const form = useAppForm({
        defaultValues: { provider: provider.value },
        onSubmit: async () => {
            router.put(`${route('third-parties.update')}?provider=${provider.value}`, { variables: variables });
        },
    });

    const handleVariableChange = (key: string, value: string) => {
        setVariables((prev) => ({ ...prev, [key]: value }));
    };

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                e.stopPropagation();
                form.handleSubmit();
            }}
        >
            <Card>
                <CardHeader>
                    <CardTitle className="text-xl">Edit {provider.display_name} Configuration</CardTitle>
                    <CardDescription>Update the variables for your {provider.display_name} integration</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Provider</label>
                        <input className="bg-muted w-full rounded-md px-3 py-2 text-sm" value={provider.display_name} disabled readOnly />
                    </div>
                    <Separator />
                    <ThirdPartyTabs
                        testContent={
                            <VariablesFormInputs provider={provider} variables={variables} tab="test" handleVariableChange={handleVariableChange} />
                        }
                        liveContent={
                            <VariablesFormInputs provider={provider} variables={variables} tab="live" handleVariableChange={handleVariableChange} />
                        }
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton label="Update Configuration" />
                    </form.AppForm>
                </CardFooter>
            </Card>
        </form>
    );
}
