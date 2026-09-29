# Vercel and Supabase setup

The public site and admin pages are static files served by Vercel. Supabase provides authentication and the database; PHP is not used by this deployment.

## Supabase

1. Create a Supabase project.
2. Open `supabase/schema.sql` from this repository, copy its entire contents, paste them into a new query in the Supabase SQL Editor, and click **Run**. Do not paste the filename or quotes; `supabase/schema.sql` is a path, not SQL.
3. In Supabase Authentication settings, disable public sign-ups. Create the admin account yourself from the Users page.
4. Add that account to the admin allowlist in the SQL Editor, replacing the email:

   ```sql
   insert into public.admin_users (user_id)
   select id from auth.users where email = 'admin@example.com'
   on conflict (user_id) do nothing;
   ```

5. Copy the project URL and the publishable key (or legacy `anon` key) into `js/supabase-config.js`. These values are public browser credentials; never put a `service_role` or secret key in this file.

Row-level security permits anonymous visitors to insert new visit requests but not read them. Only authenticated users listed in `admin_users` can read, update, or delete requests.

## Vercel

1. Import `Charan-K-Patil/Groving-soil-website` from GitHub in Vercel.
2. Leave the framework preset as **Other** and leave build and output directory settings empty; the site is served from the repository root.
3. Deploy. The public site is the deployment URL, and the admin sign-in page is at `/admin/`.
4. In Supabase Authentication URL settings, set the Site URL to the Vercel deployment URL. Add that URL to the redirect URL allowlist if email-based sign-in or recovery links are enabled.

After updating `js/supabase-config.js`, commit and push it to trigger a Vercel redeploy. Test a public visit request, then sign in at `/admin/` and verify the CRM.