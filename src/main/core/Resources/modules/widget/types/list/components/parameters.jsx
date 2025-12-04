import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {FormContent} from '#/main/app/content/form'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'

import {getSource} from '#/main/app/data/sources'
import {ListWidget as ListWidgetTypes} from '#/main/core/widget/types/list/prop-types'

class ListWidgetParameters extends Component {
  constructor(props) {
    super(props)

    this.state = {
      source: undefined
    }
  }

  componentDidMount() {
    this.loadSourceDefinition(this.props.instance.source)
  }

  componentDidUpdate(prevProps) {
    if (this.props.instance.source !== prevProps.instance.source) {
      this.loadSourceDefinition(this.props.instance.source)
    }
  }

  loadSourceDefinition(source) {
    getSource(source, this.props.currentContext.type, this.props.currentContext.data, {}, this.props.currentUser).then(sourceDefinition => this.setState({
      source: sourceDefinition
    }))
  }

  render() {
    if (!this.state.source) {
      return null
    }

    return (
      <>
        {'workspace' === this.props.contextType && !!get(this.props.contextData, 'id') &&
          <FormContent
            className="mb-5"
            flush={true}
            level={5}
            name={this.props.name}
            definition={[
              {
                title: trans('general'),
                primary: true,
                fields: [
                  {
                    name: 'parameters.all',
                    type: 'boolean',
                    label: trans('Afficher tous les éléments', {}, 'resource'),
                    help: trans('Par défaut seuls les élements de l\'espace courant seront affichés. Vous pouvez activer cette option pour afficher tous les éléments disponibles.')
                  }
                ]
              }
            ]}
          />
        }

        <ListForm
          level={5}
          flush={true}
          name={this.props.name}
          dataPart="parameters"
          list={this.state.source}
          parameters={this.props.instance.parameters}
        />
      </>
    )
  }
}

ListWidgetParameters.propTypes = {
  name: T.string.isRequired,
  currentUser: T.object,
  contextType: T.string.isRequired,
  contextData: T.object,
  currentContext: T.shape({
    type: T.string,
    data: T.object
  }).isRequired,
  instance: T.shape(
    ListWidgetTypes.propTypes
  ).isRequired
}

export {
  ListWidgetParameters
}
