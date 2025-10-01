# AI Response Templates and Mood System

This guide explains how the plugin combines Template Types and the Mood System to build high‑quality prompts for AI response generation. It also covers the two‑step flow (suggestion → generation), how to extend templates and moods, and how to validate outputs.

## Overview
- Templates define the structure, intent, and constraints of the reply (e.g., enthusiastic 5‑star, defective product, shipping issue).
- Moods define the tone and style of the reply (e.g., professional, friendly, concise).
- The system first suggests an appropriate template and mood based on the review, then generates the final response using those selections.

## Where Things Live
- Template types: `includes/LLM/Prompts/TemplateType.php` and concrete template implementations in `includes/LLM/Prompts/Templates/`.
- Mood types: `includes/LLM/Prompts/Moods/`.
- Prompt builder: `includes/LLM/PromptBuilder.php`.
- Validation and sanitization: `includes/Validation/*`.

## Two‑Step Flow

1) Suggest Template + Mood
- Input: review data (rating, comment text, product context).
- Output: `{ template_type, mood }` suggested for this review.
- Purpose: transparency and control. The user can accept or override.

2) Generate Response
- Input: review data + selected `template_type` + selected `mood`.
- Output: AI reply text, validated and sanitized before insertion into the reply textarea.

For a visual overview, see the Mermaid diagram in `docs/plugin-architecture.md` (Data Flow section).

## Prompt Composition

When generating a reply, the prompt builder assembles a structured instruction set containing:

- System guidelines
  - Stay on brand and follow the selected mood.
  - Be helpful, empathetic, and concise; avoid legal/medical claims.
  - Do not promise refunds/replacements; direct the user to official support channels when appropriate.
  - Respect store policy and avoid exposing internal details.

- Review context
  - Product name and short description.
  - Review rating and comment text.
  - Reviewer name (if available).

- Template instructions (structure + scenario‑specific rules)
  - Example: shipping issue → apologize, acknowledge delay, provide next steps.
  - Example: defective product → acknowledge issue, suggest contacting support, avoid blame.

- Mood adjustments (tone + style)
  - Professional: formal, clear, courteous.
  - Friendly: warm, approachable, human.
  - Concise: direct, minimal, to the point.

### Example Prompt (Simplified)

```text
You are a customer support assistant for an online store.

BRAND TONE (mood=Friendly):
- Warm, approachable, positive.
- Keep sentences short and easy to read.

SCENARIO TEMPLATE (template_type=shipping_issue):
- Acknowledge delay and empathize.
- Avoid promises; point to support if needed.
- Offer practical next steps.

PRODUCT CONTEXT:
- Name: {{ product_name }}
- Description: {{ product_description }}

REVIEW:
- Rating: {{ rating }}/5
- Author: {{ author }}
- Comment: {{ comment }}

OUTPUT REQUIREMENTS:
- Write 1 short paragraph (2–4 sentences).
- Avoid marketing fluff; be specific and helpful.
- If needed, direct to support: {{ store_support_contact }}.
```

The actual prompts are assembled by the Prompt Builder at runtime to ensure consistency and reusability across scenarios.

## Template Selection Logic (Suggestion Phase)

During suggestion, the AI is asked to classify the review into one of the supported template types and choose a mood that best fits the situation. Typical heuristics:
- Very positive review + high rating → positive/enthusiastic templates; friendly or professional mood.
- Mixed review with actionable feedback → positive with critique; professional or concise mood.
- Negative review citing defects/shipping/value concerns → respective problem template; professional or concise mood.

The UI shows the suggested combination and lets the user confirm or pick another pair before generation.

## Validation and Sanitization

After generation, responses are validated to ensure they:
- Are non‑empty and within expected length constraints.
- Do not contain sensitive data or unsupported promises.
- Are safe to render (sanitized before insertion into the admin UI).

## Extending Templates

1) Add a new template type to `TemplateType.php`.
2) Create a new template implementation in `includes/LLM/Prompts/Templates/` that:
   - Declares its scenario rules and structural guidance.
   - Works with the existing Prompt Builder contract.
3) Consider how the template should influence mood choices (if any).
4) Update documentation and, if needed, add sample data to the seeder for testing.

## Extending Moods

1) Add a new mood type in `includes/LLM/Prompts/Moods/`.
2) Define clear tone/style rules (sentence length, formality, empathy level, etc.).
3) Ensure the Prompt Builder applies mood modifiers consistently across all templates.

## Testing the Flow

- Seed sample reviews:
  - `npm run db:seed` (or `npm run db:seed:force`).
- Generate an AI response for a review:
  - `wp ai-review test <comment_id>` (runs the server‑side pipeline end‑to‑end).

Use these commands to quickly iterate on templates, moods, and prompt structure.

## Localization Notes

- All user‑facing admin UI text is localized via the plugin’s localization system.
- Prompts themselves are authored for the AI model. If you target non‑English responses, ensure the prompt explicitly specifies the output language or that the template supports multilingual output.

---

By separating scenario structure (templates) from tone (moods), the plugin produces consistent, on‑brand replies while remaining flexible enough to handle a wide range of review situations.


