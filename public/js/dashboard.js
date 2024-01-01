let cpuBar = undefined;
let memoryBar = undefined;
let swapBar = undefined;
let disk = undefined;

function getSystemInfo() {
    axios
        .get("/dashboard")
        .then((response) => {
            const systemInfo = response.data.systemInfo;
            if (systemInfo) {
                cpuBar.animate(systemInfo.cpuUsage / 100);
                memoryBar.animate(
                    (systemInfo.memoryUsage * 100) / systemInfo.memory / 100
                );
                swapBar.animate(
                    (systemInfo.swapUsage * 100) / systemInfo.swap / 100
                );
                const diskInfo = convertDiskToMB(
                    systemInfo.disk,
                    systemInfo.diskUsage
                );
                diskBar.animate(
                    (diskInfo.diskUsage * 100) / diskInfo.diskSize / 100
                );
                // document.getElementById("cpuUsage").innerText = Math.round(
                //     systemInfo.cpuUsage
                // );
                document.getElementById("memoryUsage").innerText =
                    convertMbToGb(systemInfo.memoryUsage);
                document.getElementById("memory").innerText = convertMbToGb(
                    systemInfo.memory
                );
                document.getElementById("swapUsage").innerText = convertMbToGb(
                    systemInfo.swapUsage
                );
                document.getElementById("swap").innerText = convertMbToGb(
                    systemInfo.swap
                );
                document.getElementById("diskUsage").innerText =
                    systemInfo.diskUsage;
                document.getElementById("disk").innerText = systemInfo.disk;
                document.getElementById("upTime").innerText = systemInfo.upTime;
            }
        })
        .catch((error) => {
            // console.error(error);
        });
}

function setUpProgressBars() {
    cpuBar = createProgressBar("#cpuProgressBar");
    memoryBar = createProgressBar("#memoryProgressBar");
    swapBar = createProgressBar("#swapProgressBar");
    diskBar = createProgressBar("#diskProgressBar");
}

function convertDiskToMB(diskSize, diskUsage) {
    // If diskSize has a G, remove G letter and then multiply by 1000
    if (diskSize) {
        diskSize = diskSize.replace(/\s+G/g, "");
        diskSize *= 1000;
    }
    // else if diskSize has M, remove M letter
    else {
        diskSize = diskSize.replace(/\s+M/g, "");
    }

    // Do the same for diskUsage
    if (diskUsage.includes("G")) {
        diskUsage = diskUsage.replace(/\s+G/g, "");
        diskUsage *= 1000;
    } else {
        diskUsage = diskUsage.replace(/\s+M/g, "");
    }

    return { diskSize, diskUsage };
}

function convertMbToGb(number) {
    return number < 1000 ? number + " M" : (number / 1000).toFixed(1) + " G";
}

function createProgressBar(id) {
    let bar = new ProgressBar.SemiCircle(id, {
        strokeWidth: 4,
        color: "#22c55e",
        trailColor: "#eee",
        trailWidth: 1,
        easing: "easeInOut",
        duration: 1000,
        svgStyle: null,
        text: {
            value: "",
            alignToBottom: false,
        },
        from: { color: "#22c55e" },
        to: { color: "#ef4444" },
        // Set default step function for all animate calls
        step: (state, bar) => {
            bar.path.setAttribute("stroke", state.color);
            var value = Math.round(bar.value() * 100);
            if (value === 0) {
                bar.setText("0 %");
            } else {
                bar.setText(value + " %");
            }

            bar.text.style.color = state.color;
        },
    });
    bar.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
    bar.text.style.fontSize = "2rem";

    return bar;
}

addEventListener("load", (event) => {
    setUpProgressBars();
    getSystemInfo();
    setInterval(getSystemInfo, 2000);
});
