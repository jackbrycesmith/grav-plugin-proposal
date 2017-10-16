# 📝 Proposal Plugin for [Grav CMS](https://github.com/getgrav/grav)

This plugin provides additional functionality to Grav that could help you avoid the pains of a traditional sales process:

1. *Write your proposal as a `.doc` or `.pdf`*
2. *Email it your client*
3. *Wait for a response...*
4. *If they agree, wait for them to bank transfer a deposit...*

You can **make it easier for your client to say yes** to whatever you are proposing if you:

1. Write your proposal as a 🔐 page on your website
2. Support your words with embedded media 📹🖼️
3. Let them instantly agree & pay the deposit by 💳 

This idea isn't completely new as a number of paid online proposal services already exist. See how you could achieve comparable results with Grav & the Proposal Plugin:

| Features  | **[grav-plugin-proposal](https://github.com/jackbrycesmith/grav-plugin-proposal)** | [betterproposals.io](https://betterproposals.io) | [proposify.biz](https://www.proposify.biz/) | [proppy.io](https://proppy.io) |
| ------------- | :---: | :---: | :---: | :---: |
| Responsive Template  | ☑️  | ☑️ | ☑️ | ☑️ |
| Define a Cover Image 🖼️ | ☑️  | ☑️ | ☑️ | ☑️ |
| Include private media 🤐  | ☑️ |  |
| Pay a deposit with Stripe 💳 | ☑️ | ☑️ | ☑️ | ☑️ |
| 🍎 Pay (& others) | ☑️ |  | |
| Digital Signature ✍️ |  | ☑️ | ☑️ | ☑️ |
| Custom domain 🌐 | ☑️ | ☑️ | ☑️ | ☑️ |
| Free & unlimited users 👥  | ☑️ |  | | |
| Free & unlimited proposals 📝 | ☑️ |  | | |

## How it works

After you have [installed the Proposal Plugin](#installation), add [your Stripe Publishable & Secret keys](https://stripe.com/docs/dashboard#api-keys) - this can be done through the Admin Panel:

![proposal-config-screenshot](https://user-images.githubusercontent.com/13235268/31609132-d1d643ea-b26a-11e7-887b-117dfb25628a.png)

Then set what currency you want to receive deposits in:

![cc-codes](https://user-images.githubusercontent.com/13235268/31609194-0fb83114-b26b-11e7-8918-61e0752ee3c5.png)

> ⚠️ To support Apple Pay you need to [complete an additional verification step](https://stripe.com/docs/elements/payment-request-button#verifying-your-domain-with-apple-pay) 

Now you'll be able to **write your proposals in markdown** (`proposal.md`) that will use a self-contained template (`proposal.html.twig`) to transform them into attractive pages that **look & read great on any device**.

![proposal-admin-screenshot](https://user-images.githubusercontent.com/13235268/31609136-db89dc9e-b26a-11e7-8a3a-f65bbeed9ae4.png)

### Template features

  - table of contents (inc. smooth scroll to sections) generated from `##` `###` section headings in your proposal
  - images/video can stretch to full width (medium.com-esque) 
  - uses system fonts for performance
  - `>` make quotes that stand out
  - highlight important points with `<mark>Important Point</mark>`

### Proposal options

- Select an image to cover the opening section
- Decide how much of a deposit you want the client to make to accept the proposal in the page frontmatter e.g. `deposit: 700`
- Choose who you want to be able to access the page, thanks to the [Login plugin](https://github.com/getgrav/grav-plugin-login)

![proposal-options-screenshot](https://user-images.githubusercontent.com/13235268/31609153-e7415828-b26a-11e7-9a36-8029e25107d3.png)

### End result

![proposal-payment-request](https://user-images.githubusercontent.com/13235268/31609157-ee093432-b26a-11e7-9adf-856161221f6e.gif)

<details>
<summary>
<h2 id="installation">Installation</h2>
</summary>
<p>Installing the Proposal plugin can be done in one of three ways. If you use the <a href="https://github.com/getgrav/grav-plugin-admin">Grav Admin Panel</a> the Proposal plugin should be available to install within a few clicks. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.</p>
<h3 id="gpm-installation">GPM Installation</h3>
<p>Install the Proposal plugin via the <a href="https://learn.getgrav.org/advanced/grav-gpm">Grav Package Manager (GPM)</a> through your system&#39;s terminal (also called the command line).  From the root of your Grav install type:</p>
<pre><code>bin/gpm install proposal</code></pre>
<p>This will install the Proposal plugin into your <code>/user/plugins</code> directory within Grav. Its files can be found under <code>/your/site/grav/user/plugins/proposal</code>.</p>
<h3 id="manual-installation">Manual Installation</h3>
<p>To install this plugin, just download the zip version of this repository and unzip it under <code>/your/site/grav/user/plugins</code>. Then, rename the folder to <code>proposal</code>. You can find these files on <a href="https://github.com/jackbrycesmith/grav-plugin-proposal">GitHub</a> or via <a href="https://getgrav.org/downloads/plugins#extras">GetGrav.org</a>.</p>
<p>You should now have all the plugin files under</p>
<pre><code>/your/site/grav/user/plugins/proposal</code></pre>
</details>
<details>
<summary><h2 id="configuration">Configuration</h2></summary>
<p>Before configuring this plugin, you should copy the <code>user/plugins/proposal/proposal.yaml</code> to <code>user/config/plugins/proposal.yaml</code> and only edit that copy.</p>
<p>Here is the default configuration and an explanation of available options:</p>
<pre><code class="lang-yaml">enabled: true
pay_route: /you-can-change-this-url-that-processes-proposal-acceptance
stripe_public_key: &#39;&#39;
stripe_secret_key: &#39;&#39;
stripe_country: GB
stripe_currency: gbp
</code></pre>
</details>

## Extra tips

- Include your own live chat into `proposal.html.twig`
- Install the [Admin User Manager Addon](https://github.com/david-szabo97/grav-plugin-admin-addon-user-manager) to easily create new users & give them access to your proposals. 
- Install the [Editor Buttons Plugin](https://github.com/getgrav/grav-plugin-editor-buttons) to make it easy to insert markdown formatted pricing tables

![admin-md-table](https://user-images.githubusercontent.com/13235268/31609175-fe8d851a-b26a-11e7-910c-25b4f2ef3907.gif)

**🌟 Made by [Jack Bryce-Smith](https://jack.bryce-smith.com) at [Markage Ltd](https://markage.uk).**
