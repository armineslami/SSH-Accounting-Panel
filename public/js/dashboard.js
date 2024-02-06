let cpuBar = undefined;
let memoryBar = undefined;
let swapBar = undefined;
let disk = undefined;

function getSystemInfo() {
    axios
        .get("/api/dashboard")
        .then((response) => {
            const systemInfo = response.data.systemInfo;
            if (systemInfo) {
                cpuBar.animate(systemInfo.cpuUsage / 100);
                memoryBar.animate(
                    systemInfo.memoryUsage === "0" || systemInfo.memory === "0" ?
                        0 : (systemInfo.memoryUsage * 100) / systemInfo.memory / 100
                );
                swapBar.animate(
                    systemInfo.swapUsage === "0" || systemInfo.swap === "0" ?
                        0 : (systemInfo.swapUsage * 100) / systemInfo.swap / 100
                );
                const diskInfo = convertDiskToMB(
                    systemInfo.disk,
                    systemInfo.diskUsage
                );
                diskBar.animate(
                    diskInfo.diskUsage === "0" || diskInfo.diskSize === "0" ?
                        0 : (diskInfo.diskUsage * 100) / diskInfo.diskSize / 100
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
                document.getElementById("upTimeFull").innerText = systemInfo.upTime;
                document.getElementById("upTime").innerText = extractTimeComponents(systemInfo.upTime);
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
    const screenWidth = window.innerWidth
        || document.documentElement.clientWidth
        || document.body.clientWidth;

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
    bar.text.style.fontSize = screenWidth < 375 ? "1.3rem" : (screenWidth < 414 ? "1.6rem" : "1.9");

    return bar;
}

function extractTimeComponents(text) {
    // Define regular expressions for weeks, days, hours, and minutes
    const weeksRegex = /(\d+)\s*weeks?/;
    const daysRegex = /(\d+)\s*days?/;
    const hoursRegex = /(\d+)\s*hours?/;
    const minutesRegex = /(\d+)\s*minutes?/;

    // Initialize variables to store extracted values
    let weeks = 0, days = 0, hours = 0, minutes = 0;

    // Match and extract weeks
    const weeksMatch = text.match(weeksRegex);
    if (weeksMatch) {
        weeks = parseInt(weeksMatch[1]);
    }

    // Match and extract days if weeks not present or weeks are zero
    const daysMatch = text.match(daysRegex);
    if (daysMatch) {
        days = parseInt(daysMatch[1]);
    }

    // Match and extract hours if days not present
    const hoursMatch = text.match(hoursRegex);
    if (hoursMatch) {
        hours = parseInt(hoursMatch[1]);
    }

    // Match and extract minutes if hours not present
    const minutesMatch = text.match(minutesRegex);
    if (minutesMatch) {
        minutes = parseInt(minutesMatch[1]);
    }

    // Build the result string based on the extracted values
    let result = "";
    if (weeks > 0) {
        result = `${weeks} week${weeks > 1 ? 's' : ''}`;
    } else if (days > 0) {
        result = `${days} day${days > 1 ? 's' : ''}`;
    } else if (hours > 0) {
        result = `${hours} hour${hours > 1 ? 's' : ''}`;
    } else {
        result = `${minutes} min${minutes > 1 ? 's' : ''}`;
    }

    return result;
}

addEventListener("load", (event) => {
    setUpProgressBars();
    getSystemInfo();
    setInterval(getSystemInfo, 2000);
});
