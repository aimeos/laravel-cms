import { BatchHttpLink } from "apollo-link-batch-http"
import { createApolloProvider } from '@vue/apollo-option'
import { ApolloClient, ApolloLink, InMemoryCache } from '@apollo/client/core'
import createUploadLink from 'apollo-upload-client/createUploadLink.mjs'


const node = document.querySelector('#app')
const httpLink = ApolloLink.split(
  operation => operation.getContext().hasUpload,
  createUploadLink({
    uri: node?.dataset?.urlgraphql || '/graphql',
    credentials: 'include'
  }),
  new BatchHttpLink({
    uri: node?.dataset?.urlgraphql || '/graphql',
    batchMax: 50,
    batchInterval: 20,
    credentials: 'include'
  })
)

const apolloClient = new ApolloClient({cache: new InMemoryCache(), link: httpLink})
const apollo = createApolloProvider({defaultClient: apolloClient})

export default apollo
export { apolloClient }