import modalTemplate from './../Partial/confirm.html'

export default class ConfirmService {
  constructor($uibModal) {
    this.$uibModal = $uibModal
  }

  open(options, callback) {
    let title, message, confirmButton

    // Get modal options
    if (options) {
      if (options.title) {
        title = options.title
      }

      if (options.message) {
        message = options.message
      }

      if (options.confirmButton) {
        confirmButton = options.confirmButton
      }
    }

    // Display confirm modal
    const modalInstance = this.$uibModal.open({
      template: modalTemplate,
      controller: 'ConfirmModalCtrl',
      resolve: {
        title: () => title,
        message: () => message,
        confirmButton: () => confirmButton
      }
    })

    // If callback defined, execute it on confirm
    if (callback) {
      modalInstance.result.then(callback)
    }
  }
}
