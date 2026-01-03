import axios from 'axios'
import { API_BASE_URL } from './urls'

const ApiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

export default ApiClient
