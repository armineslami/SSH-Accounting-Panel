@props([
    "token",
    'name',
    'show' => false,
    'maxWidth' => '4xl'
])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl'
    ][$maxWidth];
@endphp

<div
    id="terminal-container"
    x-data="{
        canClose: false,
        canReload: false,
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    @terminate="canClose = true"
    @reload="canReload = true"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
{{--    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"--}}
    x-on:close.stop="canClose ? show = false : show = true; canReload ? window.location.reload() : null;"
    x-on:keydown.escape.window="canClose ? show = false : show = true; canReload ? window.location.reload() : null;"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="canClose ? show = false : show = true; canReload ? window.location.reload() : null;"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-900 dark:bg-gray-600 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 overflow-hidden transform transition-all h-full sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-on:click="canClose ? show = false : show = true; canReload ? window.location.reload() : null;"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div class="h-full">
            <div class="h-full">
                <div class="h-full px-0 sm:px-8">
                    <div id="terminal"
                         class="h-full relative p-4 font-light text-md bg-gray-900 dark:bg-gray-900 text-gray-100 dark:text-gray-100 shadow-sm rounded-lg">
                        <div class="absolute top-0 left-0 right-0 p-4">
                            <span class="inline-block me-1 w-3 h-3 bg-red-500 rounded-full relative cursor-pointer text-red-500 hover:text-gray-600"
                                  x-on:click="canClose ? show = false : show = true; canReload ? window.location.reload() : null;">
                                <span class="absolute -top-0.5 left-1/3 text-2xs">x</span>
                            </span>
                            <span class="inline-block me-1 w-3 h-3 bg-yellow-500 rounded-full"></span>
                            <span class="inline-block me-1 w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="p-4">SAP Terminal</span>
                        </div>
                        <div id="terminal-body" class="mt-12 p-0 pb-12 max-h-full overflow-y-auto">
                            Connecting ...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    var failed = false;

    addEventListener("load", () => {
        const token = @json($token);

        if (!token)
            return;

        const terminalBody = document.getElementById('terminal-body');
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

        axios({
            method: 'post',
            url: '/api/terminal',
            data: {token: token},
            responseType: 'text', // or stream
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            onDownloadProgress: function (response) {
                // console.log("Response => ", response);
                const string = response.event.target.response;
                terminalBody.innerHTML = string;
                terminalBody.scrollTop = terminalBody.scrollHeight;

                if (
                    string.includes("Failed to run the task") ||
                    string.includes("Operation timed out") ||
                    string.includes("Connection refused") ||
                    string.includes("Permission denied, please try again.") ||
                    string.includes("Connection test failed") ||
                    string.includes("sudo: a terminal is required") ||
                    string.includes("Inbound not found") ||
                    string.includes("Missing arguments") ||
                    string.includes("Failed to set up the server") ||
                    string.includes("Unsupported OS")
                ) {
                    failed = true;
                }
            },
        })
            .then(response => {
                // console.log("Completed: ", response)
                let error = undefined;
                if (response.status !== 200) {
                    try {
                        const res = JSON.parse(response.data)
                        error = "<p class='text-red-500'>" + res.message + "<p>";
                    } catch (err) {
                        error = "<p class='text-red-500'>Failed to run the task<p>";
                    }
                }
                else if (!failed) {
                    allowReload();
                }

                if (error) {
                    terminalBody.innerHTML = error;
                }

                terminalBody.innerHTML += escapeMessage();

                allowTerminalTerminate();

                terminalBody.scrollTop = terminalBody.scrollHeight;
            })
            .catch(error => {
                // console.error("Error: ", error.response.data);

                try {
                    const err = JSON.parse(error.response.data)
                    terminalBody.innerHTML = "<p class='text-red-500'>" + err.message + "<p>";
                } catch (err) {
                    terminalBody.innerHTML = "<p class='text-red-500'>Connection failed<p>";
                }
                terminalBody.innerHTML += escapeMessage();
                allowTerminalTerminate();
            });
    });

    function allowTerminalTerminate() {
        const terminateEvent = new Event("terminate");
        document.getElementById("terminal-container").dispatchEvent(terminateEvent);
    }

    function allowReload() {
        const terminateEvent = new Event("reload");
        document.getElementById("terminal-container").dispatchEvent(terminateEvent);
    }

    function escapeMessage() {
        return "<p class='mt-2 text-terminal-warn'>Click or Press ESC to exit</p>";
    }
</script>
