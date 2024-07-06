import axios from "axios";
const axiosPost = async (endpoint, params = null) => {
    return await axios.post(import.meta.env.VITE_API_BASE_URL + endpoint, params)
}

export { axiosPost }
