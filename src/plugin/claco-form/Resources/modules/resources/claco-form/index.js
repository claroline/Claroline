
import {declareResource} from '#/main/core/resource'
import {ClacoFormResource} from '#/plugin/claco-form/resources/claco-form/containers/resource'

/**
 * ClacoForm resource application.
 */
export default declareResource(ClacoFormResource)
  .addPermissions({
    contribute: {
      order: 1,
      actions: [
        'Créer, modifier et supprimer de nouvelles fiches'
      ]
    },
    follow: {
      order: 10,
      actions: [
        'Exporter toutes les fiches',
        'Réassigner les catégories automatiques des fiches'
      ]
    },
    edit: {
      order: 20,
      actions: [
        'Administrer toutes les fiches'
      ]
    }
  })
