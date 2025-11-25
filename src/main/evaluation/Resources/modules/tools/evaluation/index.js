import {declareTool, CommandPalette} from '#/main/core/tool'

import {EvaluationTool} from '#/main/evaluation/tools/evaluation/containers/tool'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

/**
 * Evaluation tool application.
 */
export default declareTool(EvaluationTool, () => new CommandPalette('evaluation')
  .addCommands([
    {
      name: 'add-sequence',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-route',
      label: trans('Ajouter une séquence', {}, 'command'),
      callback: () => true
    }, {
      name: 'correct-evaluations',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-check-double',
      label: trans('Corriger des évaluations en attente', {}, 'command'),
      callback: () => true
    }
  ])
).addPermissions({
  follow: {
    order: 5,
    actions: [
      'Voir le tableau de bord de l\'outil',
      'Voir le tableau de bord des séquences',
      'Initialiser les évaluations',
      'Recalculer les évaluations',
      'Télécharger les certificats des utilisateurs',
      'Régénérer les certificats des utilisateurs'
    ]
  },
  edit: {
    order: 10,
    actions: [
      'Créer des séquences',
      'Voir les séquences non publiées',
      'Administrer toutes les séquences',
      'Purger les évaluations des séquences'
    ]
  }
})
