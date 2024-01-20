@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['type' => 'file', 'class' =>
'file:mr-5 file:h-10 file:px-4 file:py-2 file:border-none file:border-[1px] file:rounded-md file:text-xs file:font-semibold
file:bg-gray-800 file:dark:bg-gray-200 file:border-gray-200 file:text-white file:dark:text-gray-800 file:uppercase
file:tracking-widest file:hover:bg-gray-700 file:dark:hover:bg-white file:cursor-pointer file:text-stone-700
h-10 w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:placeholder-gray-400
cursor-pointer focus:outline-none rounded-md shadow-sm']) !!}>
