/**
 * Returns true if it is in dark mode and changes the theme
 */
export const toggleTheme = () => {
    const isDarkMode = document.documentElement.classList.toggle('dark-mode');
    localStorage.setItem('dark-mode', isDarkMode);
    return isDarkMode;
}

/**
 * Initialize the theme
 */
export const initTheme = () => {
    const savedMode = localStorage.getItem('dark-mode');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDarkMode = savedMode ? savedMode === 'true' : systemPrefersDark;

    
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
    } else {
        document.documentElement.classList.remove('dark-mode');
    }
    
    return isDarkMode;
}