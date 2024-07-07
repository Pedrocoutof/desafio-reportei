import axios from "axios";
const axiosPost = async (endpoint, params = null) => {
    return await axios.post(import.meta.env.VITE_API_BASE_URL + endpoint, params)
    // return await axios.post("https://desafio-reportei.codejr.com.br/api/" + endpoint, params)
}

export { axiosPost }
