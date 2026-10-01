<!--
================================================================================
IQArchive v2 — Add Administrative Office Modal
File: resources/js/Pages/Documents/Common-Documents/Partials/AddOfficeModal.vue
Role: Modal dialog for registering new institutional administrative offices with icon picker.
UI Standard: DaisyUI modal, input, textarea, btn, fieldset.
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Building2, X, AlertCircle, ChevronDown } from 'lucide-vue-next';
import { OFFICE_ICON_OPTIONS, OFFICE_ICON_MAP } from './officeIcons';

const props = defineProps({
    show: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    description: '',
    icon: 'building-2',
});

const selectedIconLabel = computed(() => {
    return OFFICE_ICON_OPTIONS.find((item) => item.id === form.icon)?.label || 'Administration / General';
});

const selectedIconComponent = computed(() => OFFICE_ICON_MAP[form.icon] || Building2);

function closeModal() {
    form.clearErrors();
    form.reset();
    emit('close');
}

function selectIcon(iconId) {
    form.icon = iconId;
    if (document.activeElement && typeof document.activeElement.blur === 'function') {
        document.activeElement.blur();
    }
}

function submit() {
    form.post('/documents/offices', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
}
</script>

<template>
    <div
        class="modal"
        :class="{ 'modal-open': show }"
        role="dialog"
        aria-modal="true"
        aria-labelledby="add-office-modal-title"
        @keydown.esc="closeModal"
    >
        <div class="modal-box max-w-md p-6 bg-base-100 rounded-xl shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2.5">
                    <div>
                        <h3 id="add-office-modal-title" class="text-base font-bold text-slate-800">
                            Add Administrative Office
                        </h3>
                    </div>
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-ghost btn-circle text-slate-400 hover:text-slate-600"
                    @click="closeModal"
                    aria-label="Close modal"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div v-if="form.hasErrors && form.errors.error" class="alert alert-error text-xs p-3">
                    <AlertCircle class="w-4 h-4 shrink-0" />
                    <span>{{ form.errors.error }}</span>
                </div>

                <!-- Office Name & Icon Prefix Dropdown -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs flex items-center justify-between">
                        <span>Office Name</span>
                    </legend>
                    <div class="join w-full">
                        <!-- Icon Selector Dropdown -->
                        <div class="dropdown dropdown-bottom">
                            <button
                                tabindex="0"
                                type="button"
                                class="btn btn-sm join-item bg-slate-50 hover:bg-slate-100 border border-slate-300 text-slate-700 px-2.5 gap-1.5 focus-visible:outline-none"
                                :title="`Icon: ${selectedIconLabel} (Click to change)`"
                                aria-label="Change office icon"
                            >
                                <component :is="selectedIconComponent" class="w-4 h-4 text-bu-blue-700" />
                                <ChevronDown class="w-3 h-3 text-slate-400" />
                            </button>
                            <div
                                tabindex="0"
                                class="dropdown-content z-30 p-2.5 shadow-xl bg-base-100 border border-slate-200 rounded-xl w-68 mt-1.5"
                            >
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2 px-1">
                                    Select Icon
                                </div>
                                <div class="grid grid-cols-4 gap-1.5">
                                    <button
                                        v-for="opt in OFFICE_ICON_OPTIONS"
                                        :key="opt.id"
                                        type="button"
                                        @click="selectIcon(opt.id)"
                                        class="btn btn-sm btn-ghost p-1 flex items-center justify-center rounded-lg border transition-all"
                                        :class="form.icon === opt.id ? 'bg-bu-blue-50 border-bu-blue-300 text-bu-blue-700 ring-1 ring-bu-blue-400' : 'border-transparent hover:bg-slate-100 text-slate-600'"
                                        :title="opt.label"
                                        :aria-label="opt.label"
                                    >
                                        <component :is="opt.icon" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Name Input -->
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Office of Student Affairs and Services"
                            class="input input-bordered input-sm join-item grow"
                            :class="{ 'input-error': form.errors.name }"
                            maxlength="100"
                            required
                            autofocus
                        />
                    </div>
                    <span v-if="form.errors.name" class="text-xs text-error mt-1">{{ form.errors.name }}</span>
                    <span v-if="form.errors.icon" class="text-xs text-error mt-1">{{ form.errors.icon }}</span>
                </fieldset>

                <!-- Description / Mandate -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                        Description <span class="text-slate-400 font-normal">(Optional)</span>
                    </legend>
                    <textarea
                        v-model="form.description"
                        class="textarea textarea-bordered textarea-sm w-full"
                        :class="{ 'textarea-error': form.errors.description }"
                        rows="3"
                        placeholder="Brief summary of office mandates, university charter functions, or policy purview..."
                        maxlength="1000"
                    ></textarea>
                    <span v-if="form.errors.description" class="text-xs text-error mt-1">{{ form.errors.description }}</span>
                </fieldset>

                <!-- Actions -->
                <div class="modal-action pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-ghost text-slate-600"
                        @click="closeModal"
                        :disabled="form.processing"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-sm bg-sidebar-blue hover:bg-sidebar-blue-hover text-white border-none gap-1.5"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
                        <span>Add Office</span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="button" @click="closeModal">close</button>
        </form>
    </div>
</template>
