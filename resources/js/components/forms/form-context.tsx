import {
    FormCheckbox,
    FormDate,
    FormEmail,
    FormFile,
    FormInput,
    FormPassword,
    FormPhone,
    FormRadioGroup,
    FormRichText,
    FormSelect,
    FormSwitch,
    FormTagInput,
    FormTextarea,
    FormTime,
    FormUrl,
} from '@/components/forms/form-inputs';
import { SubscribeButton } from '@/components/forms/subscribe-button';
import { createFormHook } from '@tanstack/react-form';

import { createFormHookContexts } from '@tanstack/react-form';

export const { fieldContext, formContext, useFieldContext, useFormContext } = createFormHookContexts();

const { useAppForm } = createFormHook({
    fieldContext,
    formContext,
    fieldComponents: {
        FormInput,
        FormTextarea,
        FormSelect,
        FormFile,
        FormCheckbox,
        FormRadioGroup,
        FormSwitch,
        FormEmail,
        FormPassword,
        FormUrl,
        FormPhone,
        FormDate,
        FormTime,
        FormRichText,
        FormTagInput,
    },
    formComponents: {
        SubscribeButton,
    },
});

export interface ReusableForm<T extends Record<string, unknown>> {
    mode: 'create' | 'edit';
    defaultValues?: Partial<T>;
}

export type FormTextContext = Record<
    'create' | 'edit',
    {
        title: string;
        description: string;
        buttonText: string;
    }
>;

export { useAppForm };
