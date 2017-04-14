/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class RootCursusManagementCtrl {
  constructor(CursusService) {
    this.CursusService = CursusService
    this.cursus = CursusService.getCursus()
    this.initialize()
  }

  initialize() {
    this.CursusService.initialize()
  }

  createCursus() {
    this.CursusService.createCursus()
  }

  editCursus(cursusId) {
    this.CursusService.editCursus(cursusId)
  }

  deleteCursus(cursusId) {
    this.CursusService.deleteCursus(cursusId)
  }

  viewCursus(cursusId) {
    this.CursusService.viewRootCursus(cursusId)
  }

  importCursus() {
    this.CursusService.importCursus()
  }
}