export default defineAppConfig({
  ui: {
    container: {
      base: 'max-w-7xl w-full'
    },
    colors: {
      primary: 'emerald',
      neutral: 'zinc'
    },
    theme: {
      colors: ['primary', 'secondary', 'tertiary', 'info', 'success', 'warning', 'error']
    },
    button: {
      slots: {
        base: 'cursor-pointer',
      }
    },
    dropdownMenu: {
      slots: {
        item: 'cursor-pointer',
      }
    }
  }
})
