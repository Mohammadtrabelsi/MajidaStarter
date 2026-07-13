@props(['error' => null])

<input {{ $attributes->merge([
    'class' => 'block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:outline-none focus:ring-2 dark:text-white dark:placeholder:text-slate-500 '
        . ($error
            ? 'border-red-300 bg-red-50/50 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500/50 dark:bg-red-500/5'
            : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500/30 dark:border-white/10 dark:bg-white/5'),
]) }}>
