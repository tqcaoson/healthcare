const translations = {

    en: {
        save: 'Save',
        cancel: 'Cancel',
        patient: 'Patient',
    },

    ja: {
        save: '保存',
        cancel: 'キャンセル',
        patient: '患者',
    }
};

export function t(key) {

    const locale =
        localStorage.getItem('locale') || 'en';

    return translations[locale][key] || key;
}

export function setLocale(locale) {

    localStorage.setItem('locale', locale);

    window.location.reload();
}