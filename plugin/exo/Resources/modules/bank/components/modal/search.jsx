import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import size from 'lodash/size'
import Modal from 'react-bootstrap/lib/Modal'

import {update} from './../../../utils/utils'
import {t, tex, trans} from '#/main/core/translation'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

import {listItemMimeTypes, getDefinition} from './../../../items/item-types'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'

export const MODAL_SEARCH = 'MODAL_SEARCH'

export class SearchModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      moreFilters: false,
      filters: Object.assign({
        title: '',
        model_only: false,
        types: [],
        self_only: false,
        creators: []
      }, props.filters)
    }
  }

  updateFilters(filterName, filterValue) {
    this.setState(update(this.state, {filters: {[filterName]: {$set: filterValue}}}))
  }

  render() {
    return (
      <BaseModal {...this.props} className="search-modal">
        <Modal.Body>
          <FormGroup
            controlId="search-title"
            label={tex('filter_by_title')}
          >
            <input
              id="search-title"
              type="text"
              className="form-control"
              value={this.state.filters.title}
              onChange={e => this.updateFilters('title', e.target.value)}
            />
          </FormGroup>
          
          <div className="checkbox">
            <label htmlFor="search-self-only">
              <input
                id="search-self-only"
                type="checkbox"
                name="search-self-only"
                checked={this.state.filters.self_only}
                onChange={() => this.updateFilters('self_only', !this.state.filters.self_only)}
              />
              {tex('filter_by_self_only')}
            </label>
          </div>

          {!this.state.filters.self_only &&
            <FormGroup
              controlId="search-creator"
              label={tex('filter_by_creator')}
            >
              <input
                id="search-creator"
                type="text"
                className="form-control"
                onChange={() => true}
              />
            </FormGroup>
          }

          {this.state.moreFilters &&
            <FormGroup
              controlId="search-type"
              label={tex('filter_by_type')}
            >
              <select
                id="search-type"
                className="form-control"
                multiple="true"
                value={this.state.filters.types}
                onChange={e => {
                  const types = []
                  for (let i = 0; i < e.target.options.length; i++) {
                    if (e.target.options[i].selected) {
                      types.push(e.target.options[i].value)
                    }
                  }

                  return this.updateFilters('types', types)
                }}
              >
                {listItemMimeTypes().map(type =>
                  <option
                    key={type}
                    value={getDefinition(type).type}
                    role="option"
                  >
                    {trans(getDefinition(type).name, {}, 'question_types')}
                  </option>
                )}
              </select>
            </FormGroup>
          }

          {this.state.moreFilters &&
            <div className="checkbox">
              <label htmlFor="search-model">
                <input
                  id="search-model"
                  type="checkbox"
                  name="search-model"
                  value={true}
                  checked={this.state.filters.model_only}
                  onChange={() => this.updateFilters('model_only', !this.state.filters.model_only)}
                />
                {tex('filter_by_model_only')}
              </label>
            </div>
          }

          <a role="button" onClick={() => this.setState({moreFilters: !this.state.moreFilters})}>
            <span className={classes('fa fa-fw', this.state.moreFilters ? 'fa-caret-up' : 'fa-caret-right' )}></span>
            {tex(this.state.moreFilters ? 'filters_less' : 'filters_more')}
          </a>
        </Modal.Body>

        <Modal.Footer>
          {0 < size(this.props.filters) &&
            <button className="btn btn-link btn-link-danger pull-left" onClick={this.props.clearFilters}>
              <span className="fa fa-fw fa-ban"></span>
              {tex('filters_reset')}
            </button>
          }

          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('cancel')}
          </button>
          <button className="btn btn-primary" onClick={() => this.props.handleSearch(this.state.filters)}>
            {t('search')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

SearchModal.propTypes = {
  filters: T.shape({
    title: T.string,
    model_only: T.bool,
    types: T.arrayOf(T.string),
    self_only: T.bool,
    creators: T.array
  }).isRequired,
  fadeModal: T.func.isRequired,
  handleSearch: T.func.isRequired,
  clearFilters: T.func.isRequired
}
