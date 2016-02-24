/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
    
export default class CursusDescriptionModalCtrl {
        
    constructor($uibModalInstance, $sce, title, description) {
        this.$uibModalInstance = $uibModalInstance
        this.$sce = $sce
        this.title = title
        this.description = description
    }
    
    closeModal() {
        this.$uibModalInstance.close()
    }
}