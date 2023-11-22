const modal = ref(<string>'')

export const useModal = () => {
  const showModal = (name: string) => modal.value = name
  const hideModal = () => modal.value = ''

  return { modal, showModal, hideModal }
}