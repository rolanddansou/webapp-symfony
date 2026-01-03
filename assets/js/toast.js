import "toastify-js/src/toastify.css"
import Toastify from 'toastify-js'

class Toast {
    static success(message, duration= 3000) {
        Toastify({
            text: message,
            duration: duration,
            gravity: "bottom",
            position: "right",
            backgroundColor: "#2ecc71",
            stopOnFocus: true,
        }).showToast();
    }

    static error(message, duration= 3000) {
        Toastify({
            text: message,
            duration: duration,
            gravity: "bottom",
            position: "right",
            backgroundColor: "#e74c3c",
            stopOnFocus: true,
        }).showToast();
    }
}

export default Toast;
