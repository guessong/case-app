<template>
    <div class="min-h-screen bg-slate-950 font-body text-slate-200">
        <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center">
                    <span class="text-white font-heading font-bold text-sm">CL</span>
                </div>
                <h1 class="text-xl font-heading font-semibold text-white tracking-tight">
                    <slot name="header">Champions League Simulator</slot>
                </h1>
            </div>
        </header>
        <main>
            <slot />
        </main>
    </div>
</template>
