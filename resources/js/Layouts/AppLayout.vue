<template>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <header class="mb-8 text-center">
                <h1 class="text-3xl font-light text-gray-500 tracking-wide">
                    <slot name="header">Champions League Simulator</slot>
                </h1>
            </header>
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
