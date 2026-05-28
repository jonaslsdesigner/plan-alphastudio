// ─── Supabase Configuration ───────────────────────────────────────────────────
// Substitua pelos valores do seu projeto em https://app.supabase.com → Project Settings → API
const SUPABASE_URL      = 'https://csswokoboxenuopzfejr.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_pFHnVOiuBUColUnzDJAdKg_v0lpTbuD';

const { createClient } = supabase;
const db = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
