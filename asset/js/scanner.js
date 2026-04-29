function startScanner() {

    const codeReader = new ZXing.BrowserMultiFormatReader();

    codeReader.listVideoInputDevices()
    .then((devices) => {

        const deviceId = devices[0].deviceId;

        codeReader.decodeFromVideoDevice(deviceId, 'reader', (result, err) => {

            if (result) {
                const code = result.getText();

                alert("Code détecté : " + code);

                document.getElementById("code_barre").value = code;

                codeReaderreset();
            }
        });
       
    })
    
    .catch(err => cosole.error(err));     
}