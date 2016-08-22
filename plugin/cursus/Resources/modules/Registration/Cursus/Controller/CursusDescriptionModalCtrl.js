/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
    
export default class CursusDescriptionModalCtrl {
        
  constructor($uibModalInstance, title, description) {
    this.$uibModalInstance = $uibModalInstance
    this.title = title
    this.description = description
  }

  closeModal() {
    this.$uibModalInstance.close()
  }
}