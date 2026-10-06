class GraphQL {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    async query(q) {
        try {
            const response = await fetch(
                this.baseUrl,
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        query: q
                    })
                }
            );

            const result = await response.json();
            const data = result.data;

            return data;

        } catch (error) {
            console.error("Request Failed:", error);
        }
    }
};

module.exports = GraphQL;
