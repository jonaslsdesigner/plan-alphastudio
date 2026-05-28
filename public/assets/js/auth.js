// ─── Auth helpers ─────────────────────────────────────────────────────────────
const Auth = {
    _profile: null,

    async getSession() {
        const { data } = await db.auth.getSession();
        return data.session;
    },

    async requireLogin() {
        const session = await this.getSession();
        if (!session) {
            window.location.href = 'login.html';
            return null;
        }
        return session.user;
    },

    async getProfile(userId) {
        if (this._profile && this._profile.id === userId) return this._profile;
        const { data } = await db.from('profiles').select('*').eq('id', userId).single();
        this._profile = data;
        return data;
    },

    async login(email, password) {
        const { data, error } = await db.auth.signInWithPassword({ email, password });
        if (error) throw error;
        return data;
    },

    async register(name, email, password) {
        const { data, error } = await db.auth.signUp({ email, password });
        if (error) throw error;
        if (data.user) {
            await db.from('profiles').upsert({
                id: data.user.id,
                name: name.trim() || 'Usuário',
            });
        }
        return data;
    },

    async logout() {
        await db.auth.signOut();
        window.location.href = 'login.html';
    },
};
