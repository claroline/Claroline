import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ResourcesLinksModal as ResourcesLinksModalComponent} from '#/plugin/competency/modals/resources-links/components/modal'
import {actions, reducer, selectors} from '#/plugin/competency/modals/resources-links/store'

const ResourcesLinksModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      competencies: selectors.competencies(state),
      abilities: selectors.abilities(state)
    }),
    (dispatch) => ({
      loadCompetencies(nodeId) {
        dispatch(actions.fetchCompentencies(nodeId))
      },
      associateCompetency(nodeId, competency) {
        dispatch(actions.associateCompetency(nodeId, competency))
      },
      dissociateCompetency(nodeId, competency) {
        dispatch(actions.dissociateCompetency(nodeId, competency))
      },
      loadAbilities(nodeId) {
        dispatch(actions.fetchAbilities(nodeId))
      },
      associateAbility(nodeId, ability) {
        dispatch(actions.associateAbility(nodeId, ability))
      },
      dissociateAbility(nodeId, ability) {
        dispatch(actions.dissociateAbility(nodeId, ability))
      }
    })
  )(ResourcesLinksModalComponent)
)

export {
  ResourcesLinksModal
}
