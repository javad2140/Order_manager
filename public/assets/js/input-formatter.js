// assets/js/input-formatter.js (نسخه جدید و قابل استفاده مجدد)

// تابعی برای فعال‌سازی فرمت‌بندی سه رقم سه رقم برای فیلدهای مبلغ
export function initializeAmountFormatter(visibleInputId, hiddenInputId) {
    const formattedInput = document.getElementById(visibleInputId);
    const actualInput = document.getElementById(hiddenInputId);

    if (!formattedInput || !actualInput) {
        // اگر فیلدها پیدا نشدند، هشداری در کنسول نمایش می‌دهیم (می‌توانید در محیط تولید آن را غیرفعال کنید)
        // console.warn(`Formatter could not find elements: ${visibleInputId}, ${hiddenInputId}`);
        return;
    }

    // مقداردهی اولیه: اگر فیلد اصلی (hidden) مقداری دارد، فیلد نمایشی را فرمت می‌کنیم
    if (actualInput.value) {
        // اطمینان از اینکه عدد را به صورت صحیح parse کنیم تا toLocaleString کار کند
        // قبل از فرمت کردن، مطمئن می‌شویم که رشته فقط شامل اعداد باشد (برای جلوگیری از NaN)
        const rawValue = actualInput.value.replace(/[^0-9]/g, '');
        if (rawValue) {
            formattedInput.value = parseInt(rawValue, 10).toLocaleString('en-US');
        }
    }

    // گوش دادن به رویداد 'input' برای فرمت‌بندی در حین تایپ
    formattedInput.addEventListener('input', (e) => {
        // ۱. مقدار خام (فقط اعداد) را با حذف تمام کاراکترهای غیرعددی به دست می‌آوریم
        const rawValue = e.target.value.replace(/[^0-9]/g, '');
        
        // ۲. مقدار خام و عددی را در فیلد مخفی ذخیره می‌کنیم
        actualInput.value = rawValue;

        // ۳. مقدار داخل فیلد نمایشی را با ویرگول (جداکننده هزارگان) فرمت می‌کنیم
        if (rawValue) {
            e.target.value = parseInt(rawValue, 10).toLocaleString('en-US');
        } else {
            // اگر مقدار خالی شد، فیلد نمایشی را نیز خالی می‌کنیم
            e.target.value = '';
        }
    });

    // گوش دادن به رویداد 'blur' برای پاکسازی اضافی (مثلاً اگر کاربر نصفه رها کرد)
    formattedInput.addEventListener('blur', (e) => {
        const rawValue = e.target.value.replace(/[^0-9]/g, '');
        if (rawValue) {
            e.target.value = parseInt(rawValue, 10).toLocaleString('en-US');
        } else {
            e.target.value = '';
        }
    });
}
