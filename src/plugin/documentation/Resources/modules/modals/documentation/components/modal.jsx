import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'
import {ResourceCard} from '#/main/core/resource/components/card'

import {DocumentationPlayer} from '#/plugin/documentation/modals/documentation/components/player'
import {selectors} from '#/plugin/documentation/modals/documentation/store'

class DocumentationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      initialized: false
    }
  }

  openDoc(docId) {
    this.setState({initialized: false}, () => {
      this.props.open(docId).then(() => this.setState({initialized: true}))
    })
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'open', 'loadList', 'reset', 'resetFilters', 'tags', 'current')}
        className="data-picker-modal"
        bsSize="lg"
        icon="fa fa-fw fa-question-circle"
        title={trans('documentation', {}, 'documentation')}
        subtitle={get(this.props.current, 'resourceNode.name')}
        poster={get(this.props.current, 'resourceNode.poster') ? 'https://get.claroline.com/'+get(this.props.current, 'resourceNode.poster') : undefined}
        onEntered={() => {
          this.props.resetFilters([
            {property: 'tags', value: this.props.tags || [], locked: true}
          ])

          this.props.loadList().then((response) => {
            if (response.data && 1 === response.data.length) {
              // there is only one documentation, we directly open it
              this.openDoc(response.data[0].id)
            } else {
              this.setState({initialized: true})
            }
          })
        }}
        onExited={this.props.reset}
      >
        {!this.state.initialized &&
          <ContentLoader
            size="lg"
            description={trans('loading', {}, 'documentation')}
          />
        }

        {this.state.initialized && !isEmpty(this.props.current) &&
          <DocumentationPlayer
            tree={get(this.props.current, 'tree')}
          />
        }

        {this.state.initialized && isEmpty(this.props.current) &&
          <ListData
            name={selectors.LIST_NAME}
            fetch={{
              url: ['apiv2_documentation_list'],
              autoload: false
            }}
            primaryAction={(row) => ({
              type: CALLBACK_BUTTON,
              callback: () => this.openDoc(row.id)
            })}
            selectable={false}
            definition={[
              {
                name: 'name',
                type: 'string',
                label: trans('name'),
                displayed: true,
                primary: true
              }, {
                name: 'meta.description',
                type: 'string',
                label: trans('description'),
                displayed: true,
                sortable: false,
                options: {
                  long: true
                }
              }
            ]}
            card={ResourceCard}
          />
        }
      </Modal>
    )
  }
}

DocumentationModal.propTypes = {
  tags: T.arrayOf(T.string),

  // from store
  current: T.object,
  open: T.func.isRequired,
  loadList: T.func.isRequired,
  reset: T.func.isRequired,
  resetFilters: T.func.isRequired
}

export {
  DocumentationModal
}
