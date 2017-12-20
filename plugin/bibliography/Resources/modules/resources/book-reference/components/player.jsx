import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

const Player = props =>
  <div className="book-reference">
    <div>
      {props.bookReference.author}
    </div>
    <div>
      {props.bookReference.isbn}
    </div>
    <div>
      {props.bookReference.abstract}
    </div>
    <div>
      {props.bookReference.publisher}
    </div>
    <div>
      {props.bookReference.printer}
    </div>
    <div>
      {props.bookReference.publicationYear}
    </div>
    <div>
      {props.bookReference.language}
    </div>
    <div>
      {props.bookReference.pages}
    </div>
    <div>
      {props.bookReference.url}
    </div>
    <div>
      {props.bookReference.cover}
    </div>
  </div>

Player.propTypes = {
  bookReference: T.shape({
    author: T.string,
    isbn: T.string,
    abstract: T.string,
    publisher: T.string,
    printer: T.string,
    publicationYear: T.int,
    language: T.string,
    pages: T.int,
    url: T.string,
    cover: T.string
  }).isRequired
}

const ConnectedPlayer = connect(
  state => ({
    'bookReference': state.bookReference
  }),
  null
)(Player)

export {
  ConnectedPlayer as Player
}
